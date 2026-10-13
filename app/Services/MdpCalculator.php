<?php
namespace App\Services;
use InvalidArgumentException;
use OverflowException;
class MdpCalculator
{
    private const MAX_INPUT_CENTS = 999999999999999;
    public function calculate(array $sources): array
    {
        $rows = [];
        $seen = [];
        $summary = $this->emptySummary();
        foreach ($sources as $source) {
            $id = (int) ($source['id'] ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                throw new InvalidArgumentException('Financial Plan source IDs must be positive and unique.');
            }
            $seen[$id] = true;
            $row = $this->calculateSource($source);
            $rows[] = $row;
            $this->addRow($summary, $row);
        }
        return ['rows' => $rows, 'summary' => $summary];
    }
    public function calculateSource(array $source): array
    {
        $id = (int) ($source['id'] ?? 0);
        $mooe = $this->toCents($source['mooe'] ?? 0);
        $co = $this->toCents($source['capital_outlay'] ?? 0);
        $budget = $mooe + $co;
        $contract = $source['contract_amount'] ?? null;
        $ceiling = $contract === null || $contract === '' ? $budget : $this->toCents($contract);
        $cash = array_fill(1, 12, 0);
        $seenMonths = [];
        foreach (($source['targets'] ?? []) as $target) {
            $rawMonth = $target['month'] ?? null;
            if (filter_var($rawMonth, FILTER_VALIDATE_INT) === false) {
                throw new InvalidArgumentException("Financial Plan {$id} has an invalid month.");
            }
            $month = (int) $rawMonth;
            if ($month < 1 || $month > 12 || isset($seenMonths[$month])) {
                throw new InvalidArgumentException("Financial Plan {$id} has an invalid or duplicate month.");
            }
            $seenMonths[$month] = true;
            $cash[$month] = $this->toCents($target['amount'] ?? 0);
        }
        $annualCash = array_sum($cash);
        if ($budget === 0 && ($annualCash > 0 || $ceiling > 0)) {
            throw new InvalidArgumentException("Financial Plan {$id} has amounts without an MOOE/CO classification budget.");
        }
        if ($annualCash > $ceiling) {
            throw new InvalidArgumentException("Financial Plan {$id} schedules more cash than its effective budget.");
        }
        $monthlyMooe = array_fill(1, 12, 0);
        $monthlyCo = array_fill(1, 12, 0);
        $cumulativeCash = 0;
        $previousMooe = 0;
        foreach ($cash as $month => $amount) {
            $cumulativeCash += $amount;
            // Cumulative rounding retains every cent and prevents annual category drift.
            $cumulativeMooe = $budget > 0 ? $this->proportion($cumulativeCash, $mooe, $budget) : 0;
            $monthlyMooe[$month] = $cumulativeMooe - $previousMooe;
            $monthlyCo[$month] = $amount - $monthlyMooe[$month];
            $previousMooe = $cumulativeMooe;
        }
        return array_merge($source, [
            'monthly_total' => $cash,
            'monthly_mooe' => $monthlyMooe,
            'monthly_co' => $monthlyCo,
            'annual_total' => $annualCash,
            'annual_mooe' => array_sum($monthlyMooe),
            'annual_co' => array_sum($monthlyCo),
            'effective_budget' => $ceiling,
            'unscheduled' => $ceiling - $annualCash,
        ]);
    }
    public function emptySummary(): array
    {
        return [
            'monthly_mooe' => array_fill(1, 12, 0),
            'monthly_co' => array_fill(1, 12, 0),
            'monthly_total' => array_fill(1, 12, 0),
            'annual_mooe' => 0,
            'annual_co' => 0,
            'annual_total' => 0,
        ];
    }
    public function addRow(array &$summary, array $row): void
    {
        foreach (['monthly_mooe', 'monthly_co', 'monthly_total'] as $key) {
            foreach (range(1, 12) as $month) {
                $summary[$key][$month] = $this->addChecked($summary[$key][$month], $row[$key][$month]);
            }
        }
        foreach (['annual_mooe', 'annual_co', 'annual_total'] as $key) {
            $summary[$key] = $this->addChecked($summary[$key], $row[$key]);
        }
    }
    public function toCents($value): int
    {
        $value = trim((string) ($value ?? '0'));
        if (!preg_match('/^\d+(?:\.\d+)?$/D', $value)) {
            throw new InvalidArgumentException('MDP source amounts must be non-negative decimal numbers.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $digits = ltrim($whole . str_pad(substr($fraction, 0, 2), 2, '0'), '0') ?: '0';
        $limit = (string) self::MAX_INPUT_CENTS;
        if (strlen($digits) > strlen($limit) || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0)) {
            throw new InvalidArgumentException('An MDP source amount exceeds the supported range.');
        }
        $cents = (int) $digits;
        if (isset($fraction[2]) && $fraction[2] >= '5') {
            $cents++;
        }
        if ($cents > self::MAX_INPUT_CENTS) {
            throw new InvalidArgumentException('An MDP source amount exceeds the supported range.');
        }
        return $cents;
    }
    public function formatCents(int $cents, bool $dashForZero = false): string
    {
        if ($dashForZero && $cents === 0) {
            return '-';
        }
        $whole = (string) intdiv($cents, 100);
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole);
        return $whole . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
    private function addChecked(int $left, int $right): int
    {
        if ($right > PHP_INT_MAX - $left) {
            throw new OverflowException('The MDP total exceeds the supported range.');
        }
        return $left + $right;
    }
    private function proportion(int $amount, int $numerator, int $denominator): int
    {
        if ($amount === 0 || $numerator === 0) {
            return 0;
        }
        // Exact integer multiply/divide without overflowing a cents-by-cents product.
        $bits = [];
        while ($amount > 0) {
            $bits[] = $amount % 2;
            $amount = intdiv($amount, 2);
        }
        $quotient = 0;
        $remainder = 0;
        foreach (array_reverse($bits) as $bit) {
            $quotient *= 2;
            $remainder = $remainder * 2 + $bit * $numerator;
            $quotient += intdiv($remainder, $denominator);
            $remainder %= $denominator;
        }
        return $quotient + ($remainder * 2 >= $denominator ? 1 : 0);
    }
}
