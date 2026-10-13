<?php
namespace Tests\Unit;
use App\Services\MdpCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
class MdpCalculatorTest extends TestCase
{
    public function test_reference_example_activity_and_monthly_totals(): void
    {
        $calculator = new MdpCalculator();
        $sources = [
            $this->source(1, '40000.00', '0', [[7, '20000.00'], [10, '20000.00']]),
            $this->source(2, '18500.00', '0', [[8, '18500.00']]),
            $this->source(3, '80000.00', '0', [[6, '80000.00']]),
            $this->source(4, '80000.00', '0', [[11, '80000.00']]),
            $this->source(5, '20000.00', '0', [[5, '20000.00']]),
            $this->source(6, '20000.00', '0', [[6, '7500.00'], [9, '5000.00'], [12, '7500.00']]),
            $this->source(7, '13950.00', '0', [[6, '6950.00'], [11, '7000.00']]),
            $this->source(8, '13950.00', '0', [[6, '6950.00'], [11, '7000.00']]),
        ];
        $result = $calculator->calculate($sources);
        $this->assertSame(28640000, $result['summary']['annual_total']);
        $this->assertSame(28640000, $result['summary']['annual_mooe']);
        $this->assertSame(0, $result['summary']['annual_co']);
        $this->assertSame(10140000, $result['summary']['monthly_total'][6]);
        $this->assertSame(9400000, $result['summary']['monthly_total'][11]);
        $this->assertCount(8, $result['rows']);
    }
    public function test_mixed_category_proportional_split(): void
    {
        $row = (new MdpCalculator())->calculateSource($this->source(1, '120000.00', '80000.00', [[1, '50000.00']]));
        $this->assertSame(3000000, $row['monthly_mooe'][1]);
        $this->assertSame(2000000, $row['monthly_co'][1]);
        $this->assertSame(5000000, $row['annual_total']);
    }
    public function test_rounding_preserves_annual_category_and_monthly_totals(): void
    {
        $months = array_map(fn ($month) => [$month, '0.01'], range(1, 12));
        $row = (new MdpCalculator())->calculateSource($this->source(1, '0.01', '0.11', $months));
        $this->assertSame(1, $row['annual_mooe']);
        $this->assertSame(11, $row['annual_co']);
        $this->assertSame(12, $row['annual_total']);
        foreach (range(1, 12) as $month) {
            $this->assertSame(1, $row['monthly_mooe'][$month] + $row['monthly_co'][$month]);
        }
    }
    public function test_contract_amount_adjusts_the_effective_budget(): void
    {
        $source = $this->source(1, '60000.00', '40000.00', [[1, '25000.00'], [2, '25000.00']]);
        $source['contract_amount'] = '50000.00';
        $row = (new MdpCalculator())->calculateSource($source);
        $this->assertSame(3000000, $row['annual_mooe']);
        $this->assertSame(2000000, $row['annual_co']);
        $this->assertSame(0, $row['unscheduled']);
    }
    public function test_large_amounts_are_split_without_float_or_product_overflow(): void
    {
        $row = (new MdpCalculator())->calculateSource($this->source(1, '6000000000000.00', '4000000000000.00', [[1, '5000000000000.00']]));
        $this->assertSame(300000000000000, $row['annual_mooe']);
        $this->assertSame(200000000000000, $row['annual_co']);
    }
    public function test_duplicate_source_ids_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $source = $this->source(1, '100.00', '0', [[1, '100.00']]);
        (new MdpCalculator())->calculate([$source, $source]);
    }
    public function test_duplicate_months_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MdpCalculator())->calculateSource($this->source(1, '100.00', '0', [[1, '50.00'], [1, '50.00']]));
    }
    public function test_negative_source_amounts_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MdpCalculator())->calculateSource($this->source(1, '100.00', '0', [[1, '-1.00']]));
    }
    public function test_over_programming_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MdpCalculator())->calculateSource($this->source(1, '100.00', '0', [[1, '100.01']]));
    }
    public function test_positive_cash_requires_category_budgets(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new MdpCalculator())->calculateSource($this->source(1, '0', '0', [[1, '1.00']]));
    }
    public function test_money_formatting_and_decimal_rounding(): void
    {
        $calculator = new MdpCalculator();
        $this->assertSame(101, $calculator->toCents('1.005'));
        $this->assertSame('286,400.00', $calculator->formatCents(28640000));
        $this->assertSame('-', $calculator->formatCents(0, true));
    }
    private function source(int $id, string $mooe, string $co, array $months): array
    {
        return [
            'id' => $id,
            'mooe' => $mooe,
            'capital_outlay' => $co,
            'contract_amount' => null,
            'targets' => array_map(fn ($item) => ['month' => $item[0], 'amount' => $item[1]], $months),
        ];
    }
}
