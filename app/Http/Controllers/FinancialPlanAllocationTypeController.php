<?php
namespace App\Http\Controllers;
use App\Models\FinancialPlan;
use App\Models\FinancialPlanAllocationType;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
class FinancialPlanAllocationTypeController extends Controller
{
    // Administrator role IDs
    private const ADMIN_ROLE_IDS = [1, 29];
    // Allocation Type Management
    public function index(Request $request): View
    {
        $this->authorizeAllocationTypePermission('view');
        $isAdministrator = $this->isAdministrator();
        if ($isAdministrator) {
            $staffs = Staff::query()->orderBy('name')->get();
            $selectedStaffId = $request->integer('staff_id');
            if (! $selectedStaffId && $staffs->isNotEmpty()) {
                $selectedStaffId = (int) $staffs->first()->id;
            }
            if (
                $selectedStaffId &&
                ! $staffs->contains(fn ($staff) => (int) $staff->id === (int) $selectedStaffId)
            ) {
                abort(404);
            }
        } else {
            $selectedStaffId = $this->currentUserStaffId();
            $staffs = Staff::query()->where('id', $selectedStaffId)->get();
        }
        $allocationTypes = FinancialPlanAllocationType::query()
            ->where('staff_id', $selectedStaffId)
            ->ordered()
            ->get();
        return view('financial-plans.allocation-types.index', compact(
            'staffs',
            'selectedStaffId',
            'allocationTypes',
            'isAdministrator'
        ));
    }
    // Create Allocation Type
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAllocationTypePermission('add');
        $staffId = $this->resolveRequestedStaffId($request);
        $request->merge([
            'staff_id' => $staffId,
            'code' => $this->normalizeCode((string) $request->input('code')),
        ]);
        $validated = $this->validateAllocationType($request);
        $allocationType = FinancialPlanAllocationType::create([
            'staff_id' => $staffId,
            'code' => $validated['code'],
            'name' => trim($validated['name']),
            'allows_mooe' => $request->boolean('allows_mooe'),
            'allows_capital_outlay' => $request->boolean('allows_capital_outlay'),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Allocation Type created successfully.',
            'data' => $allocationType,
        ]);
    }
    // Update Allocation Type
    public function update(
        Request $request,
        FinancialPlanAllocationType $financialPlanAllocationType
    ): JsonResponse {
        $this->authorizeAllocationTypeAccess($financialPlanAllocationType, 'edit');
        $staffId = (int) $financialPlanAllocationType->staff_id;
        // Ownership cannot move between Staff/Offices
        $request->merge([
            'staff_id' => $staffId,
            'code' => $this->normalizeCode((string) $request->input('code')),
        ]);
        $validated = $this->validateAllocationType($request, $financialPlanAllocationType);
        $newAllowsMooe = $request->boolean('allows_mooe');
        $newAllowsCapitalOutlay = $request->boolean('allows_capital_outlay');
        $newIsActive = $request->boolean('is_active');
        $this->validateUsedAllocationTypeChanges(
            $financialPlanAllocationType,
            $validated['code'],
            $newAllowsMooe,
            $newAllowsCapitalOutlay,
            $newIsActive
        );
        $financialPlanAllocationType->update([
            'staff_id' => $staffId,
            'code' => $validated['code'],
            'name' => trim($validated['name']),
            'allows_mooe' => $newAllowsMooe,
            'allows_capital_outlay' => $newAllowsCapitalOutlay,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $newIsActive,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Allocation Type updated successfully.',
            'data' => $financialPlanAllocationType->fresh(),
        ]);
    }
    // Delete Allocation Type
    public function destroy(
        FinancialPlanAllocationType $financialPlanAllocationType
    ): JsonResponse {
        $this->authorizeAllocationTypeAccess($financialPlanAllocationType, 'delete');
        if ($this->allocationTypeIsUsed($financialPlanAllocationType)) {
            throw ValidationException::withMessages([
                'allocation_type' =>
                    'This Allocation Type is already being used by a Financial Plan and cannot be deleted.',
            ]);
        }
        $financialPlanAllocationType->delete();
        return response()->json([
            'success' => true,
            'message' => 'Allocation Type deleted successfully.',
        ]);
    }
    // Validate Allocation Type
    private function validateAllocationType(
        Request $request,
        ?FinancialPlanAllocationType $allocationType = null
    ): array {
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('financial_plan_allocation_types', 'code')
                    ->where(fn ($query) => $query->where(
                        'staff_id',
                        $request->integer('staff_id')
                    ))
                    ->ignore($allocationType?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'allows_mooe' => ['nullable', 'boolean'],
            'allows_capital_outlay' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if (
            ! $request->boolean('allows_mooe') &&
            ! $request->boolean('allows_capital_outlay')
        ) {
            throw ValidationException::withMessages([
                'allows_mooe' =>
                    'Select at least one expense category: MOOE or Capital Outlay.',
            ]);
        }
        return $validated;
    }
    // Protect Allocation Types already in use
    private function validateUsedAllocationTypeChanges(
        FinancialPlanAllocationType $allocationType,
        string $newCode,
        bool $newAllowsMooe,
        bool $newAllowsCapitalOutlay,
        bool $newIsActive
    ): void {
        $storedAllocationItems = $allocationType
            ->allocationItems()
            ->get(['expense_category']);
        $planRows = FinancialPlan::query()
            ->where('staff_id', $allocationType->staff_id)
            ->where('row_type', 'item')
            ->whereRaw('LOWER(TRIM(allocation_type)) = ?', [
                strtolower(trim((string) $allocationType->code)),
            ])
            ->get(['mooe', 'capital_outlay']);
        $isUsed = $storedAllocationItems->isNotEmpty() || $planRows->isNotEmpty();
        if (! $isUsed) {
            return;
        }
        if ($newCode !== $allocationType->code) {
            throw ValidationException::withMessages([
                'code' =>
                    'The code cannot be changed because this Allocation Type is already being used.',
            ]);
        }
        if (! $newIsActive) {
            throw ValidationException::withMessages([
                'is_active' =>
                    'This Allocation Type cannot be deactivated because it is already being used.',
            ]);
        }
        $usesMooe = $storedAllocationItems->contains(
            fn ($item) => strtolower(trim((string) $item->expense_category)) === 'mooe'
        ) || $planRows->contains(fn ($row) => (float) $row->mooe > 0);
        $usesCapitalOutlay = $storedAllocationItems->contains(
            fn ($item) => strtolower(trim((string) $item->expense_category)) === 'capital_outlay'
        ) || $planRows->contains(fn ($row) => (float) $row->capital_outlay > 0);
        if ($usesMooe && ! $newAllowsMooe) {
            throw ValidationException::withMessages([
                'allows_mooe' =>
                    'MOOE cannot be disabled because this Allocation Type is already used by a MOOE budget line or allocation.',
            ]);
        }
        if ($usesCapitalOutlay && ! $newAllowsCapitalOutlay) {
            throw ValidationException::withMessages([
                'allows_capital_outlay' =>
                    'Capital Outlay cannot be disabled because this Allocation Type is already used by a Capital Outlay budget line or allocation.',
            ]);
        }
    }
    // Check whether an Allocation Type is already used
    private function allocationTypeIsUsed(
        FinancialPlanAllocationType $allocationType
    ): bool {
        if ($allocationType->allocationItems()->exists()) {
            return true;
        }
        return FinancialPlan::query()
            ->where('staff_id', $allocationType->staff_id)
            ->where('row_type', 'item')
            ->whereRaw('LOWER(TRIM(allocation_type)) = ?', [
                strtolower(trim((string) $allocationType->code)),
            ])
            ->exists();
    }
    // Resolve requested Staff/Office
    private function resolveRequestedStaffId(Request $request): int
    {
        if ($this->isAdministrator()) {
            $validated = $request->validate([
                'staff_id' => ['required', 'integer', 'exists:staffs,id'],
            ]);
            return (int) $validated['staff_id'];
        }
        return $this->currentUserStaffId();
    }
    // Current user's Staff/Office
    private function currentUserStaffId(): int
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $staffId = (int) $user->staff_id;
        if ($staffId <= 0) {
            abort(403, 'Your account is not assigned to a Staff/Office.');
        }
        abort_unless(
            Staff::query()->where('id', $staffId)->exists(),
            403,
            'Your assigned Staff/Office could not be found.'
        );
        return $staffId;
    }
    // Check Allocation Type ownership
    private function authorizeAllocationTypeAccess(
        FinancialPlanAllocationType $allocationType,
        string $permission
    ): void {
        $this->authorizeAllocationTypePermission($permission);
        if ($this->isAdministrator()) {
            return;
        }
        abort_unless(
            (int) $allocationType->staff_id === $this->currentUserStaffId(),
            403,
            'You can only manage Allocation Types for your assigned Staff/Office.'
        );
    }
    // Check Allocation Type permission
    private function authorizeAllocationTypePermission(string $permission): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        if ($this->isAdministrator()) {
            return;
        }
        $allowedPermissions = ['view', 'add', 'edit', 'delete'];
        abort_unless(in_array($permission, $allowedPermissions, true), 403);
        $hasPermission = DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('modules', 'modules.id', '=', 'permissions.module_id')
            ->where('permission_role.role_id', $user->role_id)
            ->where('modules.name', 'Allocation Type Management')
            ->where('permissions.name', $permission)
            ->exists();
        abort_unless(
            $hasPermission,
            403,
            'You are not authorized to perform this Allocation Type action.'
        );
        $this->currentUserStaffId();
    }
    // Administrator access
    private function isAdministrator(): bool
    {
        $user = auth()->user();
        return $user &&
            in_array((int) $user->role_id, self::ADMIN_ROLE_IDS, true);
    }
    // Normalize code
    private function normalizeCode(string $code): string
    {
        $code = preg_replace('/[^a-zA-Z0-9]+/', '_', trim($code));
        return strtolower(trim($code, '_'));
    }
}
