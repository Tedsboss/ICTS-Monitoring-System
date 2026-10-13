<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FiscalYearController extends Controller
{
    private function hasAllocationPermission(string $permission): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->isAdministrator()) {
            return true;
        }
        return $user->role && $user->role->permissions->contains(function ($item) use ($permission) {
            return strcasecmp((string) optional($item->module)->name, 'Allocation Management') === 0
                && strcasecmp((string) $item->name, $permission) === 0;
        });
    }

    private function authorizeFiscalYear(FiscalYear $fiscalYear, string $permission): void
    {
        abort_unless($this->hasAllocationPermission($permission), 403);
        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            abort_unless($staffId !== null && (int) $staffId === (int) $fiscalYear->staff_id, 403);
        }
    }

    private function staffOptions()
    {
        return Staff::query()
            ->when(! auth()->user()->isAdministrator(), fn ($query) => $query->where('id', auth()->user()->staff_id))
            ->orderBy('name')->get(['id', 'name', 'abbreviation']);
    }

    public function index(): View
    {
        abort_unless($this->hasAllocationPermission('view'), 403);
        $query = FiscalYear::query()->with('staff')->withCount('allocations');
        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            $staffId === null ? $query->whereRaw('1 = 0') : $query->where('staff_id', (int) $staffId);
        }
        return view('fiscal-years.index', [
            'fiscalYears' => $query->orderByDesc('year')->orderBy('staff_id')->get(),
            'canAddFiscalYears' => $this->hasAllocationPermission('add'),
            'canEditFiscalYears' => $this->hasAllocationPermission('edit'),
            'canDeleteFiscalYears' => $this->hasAllocationPermission('delete'),
        ]);
    }

    public function create(): View
    {
        abort_unless($this->hasAllocationPermission('add'), 403);
        return view('fiscal-years.create', ['staffOptions' => $this->staffOptions()]);
    }

    public function edit(FiscalYear $fiscalYear): View
    {
        $this->authorizeFiscalYear($fiscalYear, 'edit');
        $fiscalYear->loadCount('allocations');
        return view('fiscal-years.edit', ['fiscalYear' => $fiscalYear, 'staffOptions' => $this->staffOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->hasAllocationPermission('add'), 403);
        $data = $this->validatedData($request);
        DB::transaction(function () use ($data) {
            Staff::whereKey($data['staff_id'])->lockForUpdate()->firstOrFail();
            $this->rejectDuplicate($data);
            FiscalYear::create($data);
        });
        return redirect()->route('fiscal-years.index')->with('success', 'Fiscal year created successfully.');
    }

    public function update(Request $request, FiscalYear $fiscalYear): RedirectResponse
    {
        $this->authorizeFiscalYear($fiscalYear, 'edit');
        $data = $this->validatedData($request);
        DB::transaction(function () use ($data, $fiscalYear) {
            Staff::whereIn('id', array_unique([(int) $fiscalYear->staff_id, (int) $data['staff_id']]))
                ->orderBy('id')->lockForUpdate()->get(['id']);
            $locked = FiscalYear::whereKey($fiscalYear->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeFiscalYear($locked, 'edit');
            if ($locked->allocations()->exists()
                && ((int) $locked->year !== (int) $data['year'] || (int) $locked->staff_id !== (int) $data['staff_id'])) {
                throw ValidationException::withMessages([
                    'year' => 'This fiscal year is used by allocations. Its year and Staff / Office cannot be changed. Create a separate fiscal year instead.',
                ]);
            }
            $this->rejectDuplicate($data, (int) $locked->id);
            $locked->update($data);
        });
        return redirect()->route('fiscal-years.index')->with('success', 'Fiscal year updated successfully.');
    }

    public function destroy(FiscalYear $fiscalYear): RedirectResponse
    {
        $this->authorizeFiscalYear($fiscalYear, 'delete');
        $deleted = DB::transaction(function () use ($fiscalYear) {
            Staff::whereKey($fiscalYear->staff_id)->lockForUpdate()->firstOrFail();
            $locked = FiscalYear::whereKey($fiscalYear->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeFiscalYear($locked, 'delete');
            if ($locked->allocations()->exists()) {
                return false;
            }
            $locked->delete();
            return true;
        });
        return redirect()->route('fiscal-years.index')->with($deleted ? 'success' : 'error', $deleted
            ? 'Fiscal year deleted successfully.'
            : 'This fiscal year cannot be deleted because it is already used by an allocation.');
    }

    private function validatedData(Request $request): array
    {
        if (! auth()->user()->isAdministrator()) {
            abort_unless(auth()->user()->staff_id !== null, 403);
            $request->merge(['staff_id' => auth()->user()->staff_id]);
        }
        return $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],
        ], ['staff_id.required' => 'Please select a Staff / Office.', 'staff_id.exists' => 'Please select an existing Staff / Office.']);
    }

    private function rejectDuplicate(array $data, ?int $ignoreId = null): void
    {
        $query = FiscalYear::where('year', $data['year'])->where('staff_id', $data['staff_id']);
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['year' => 'This fiscal year already exists for the selected Staff / Office.']);
        }
    }
}
