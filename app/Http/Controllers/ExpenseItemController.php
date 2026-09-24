<?php

namespace App\Http\Controllers;

use App\Models\ExpenseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseItemController extends Controller
{
    private const ADMIN_ROLES = [1, 29];

    private function isAdmin(): bool
    {
        return in_array(
            (int) auth()->user()->role_id,
            self::ADMIN_ROLES,
            true
        );
    }

    /**
     * Determine which Staff/Office owns the Expense Item.
     */
    private function resolveStaffId(Request $request): int
    {
        /*
         * STAFF USER
         * Always use the staff_id of the logged-in user.
         */
        if (! $this->isAdmin()) {
            $staffId = (int) auth()->user()->staff_id;

            if ($staffId <= 0) {
                throw ValidationException::withMessages([
                    'staff_id' =>
                        'Your account is not assigned to a Staff/Office.',
                ]);
            }

            return $staffId;
        }

        /*
         * ADMIN
         * Primary source = staff_id sent by the Builder.
         */
        $staffId = (int) $request->input('staff_id');

        if ($staffId > 0) {
            $exists = DB::table('staffs')
                ->where('id', $staffId)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'staff_id' =>
                        'The selected Staff/Office does not exist.',
                ]);
            }

            return $staffId;
        }

        /*
         * Admin fallback:
         * Resolve staff from office_name.
         */
        $officeName = trim(
            (string) $request->input('office_name', '')
        );

        if ($officeName !== '') {
            $resolvedStaffId = DB::table('staffs')
                ->where('name', $officeName)
                ->value('id');

            if ($resolvedStaffId !== null) {
                return (int) $resolvedStaffId;
            }
        }

        throw ValidationException::withMessages([
            'staff_id' =>
                'The Staff/Office could not be determined.',
        ]);
    }

    private function authorizeExpenseItem(
        ExpenseItem $expenseItem
    ): void {
        if ($this->isAdmin()) {
            return;
        }

        if (
            (int) $expenseItem->staff_id !==
            (int) auth()->user()->staff_id
        ) {
            abort(
                403,
                'You are not authorized to manage this Expense Item.'
            );
        }
    }

    /**
     * Get Expense Items for FY + Staff/Office.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],

            'staff_id' => [
                'nullable',
                'integer',
                'exists:staffs,id',
            ],

            'office_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'include_inactive' => [
                'nullable',
                'boolean',
            ],
        ]);

        $staffId = $this->resolveStaffId($request);

        $query = ExpenseItem::query()
            ->where(
                'fiscal_year',
                (int) $validated['fiscal_year']
            )
            ->where('staff_id', $staffId);

        if (! $request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        $items = $query
            ->orderBy('name')
            ->get([
                'id',
                'fiscal_year',
                'staff_id',
                'name',
                'is_active',
            ]);

        return response()->json($items);
    }

    /**
     * Add Expense Item.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],

            'staff_id' => [
                'nullable',
                'integer',
                'exists:staffs,id',
            ],

            'office_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $staffId = $this->resolveStaffId($request);

        $fiscalYear =
            (int) $validated['fiscal_year'];

        $name = trim(
            (string) $validated['name']
        );

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => 'Expense Item name is required.',
            ]);
        }

        /*
         * Case-insensitive duplicate check.
         */
        $existing = ExpenseItem::query()
            ->where('fiscal_year', $fiscalYear)
            ->where('staff_id', $staffId)
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [mb_strtolower($name)]
            )
            ->first();

        /*
         * Existing inactive item:
         * reactivate instead of creating duplicate.
         */
        if ($existing) {

            if (! $existing->is_active) {
                $existing->update([
                    'is_active' => true,
                    'updated_by' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' =>
                        'Expense Item reactivated.',
                    'data' => $existing->fresh(),
                ]);
            }

            throw ValidationException::withMessages([
                'name' =>
                    'This Expense Item already exists for this Staff/Office and Fiscal Year.',
            ]);
        }

        $expenseItem = ExpenseItem::create([
            'fiscal_year' => $fiscalYear,
            'staff_id' => $staffId,
            'name' => $name,
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Expense Item added successfully.',
            'data' => $expenseItem,
        ], 201);
    }

    /**
     * Rename Expense Item.
     */
    public function update(
        Request $request,
        ExpenseItem $expenseItem
    ): JsonResponse {

        $this->authorizeExpenseItem($expenseItem);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $name = trim(
            (string) $validated['name']
        );

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' =>
                    'Expense Item name is required.',
            ]);
        }

        $duplicate = ExpenseItem::query()
            ->where(
                'fiscal_year',
                $expenseItem->fiscal_year
            )
            ->where(
                'staff_id',
                $expenseItem->staff_id
            )
            ->where(
                'id',
                '!=',
                $expenseItem->id
            )
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [mb_strtolower($name)]
            )
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' =>
                    'This Expense Item already exists for this Staff/Office and Fiscal Year.',
            ]);
        }

        $expenseItem->update([
            'name' => $name,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Expense Item updated.',
            'data' => $expenseItem->fresh(),
        ]);
    }

    /**
     * Activate / deactivate Expense Item.
     */
    public function toggle(
        ExpenseItem $expenseItem
    ): JsonResponse {

        $this->authorizeExpenseItem($expenseItem);

        $expenseItem->update([
            'is_active' =>
                ! $expenseItem->is_active,

            'updated_by' =>
                auth()->id(),
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                $expenseItem->is_active
                    ? 'Expense Item activated.'
                    : 'Expense Item deactivated.',

            'data' =>
                $expenseItem->fresh(),
        ]);
    }
}