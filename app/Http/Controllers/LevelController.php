<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LevelController extends Controller
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

    private function authorizeLevel(Level $level, string $permission): void
    {
        abort_unless($this->hasAllocationPermission($permission), 403);
        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            abort_unless($staffId !== null && (int) $staffId === (int) $level->staff_id, 403);
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
        $query = Level::query()->withCount('allocations');
        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;
            $staffId === null ? $query->whereRaw('1 = 0') : $query->where('staff_id', (int) $staffId);
        }
        return view('levels.index', [
            'levels' => $query->orderBy('level_code')->orderBy('staff_id')->get(),
            'staffOptions' => $this->staffOptions(),
            'canAddLevels' => $this->hasAllocationPermission('add'),
            'canEditLevels' => $this->hasAllocationPermission('edit'),
            'canDeleteLevels' => $this->hasAllocationPermission('delete'),
        ]);
    }

    public function create(): View
    {
        abort_unless($this->hasAllocationPermission('add'), 403);
        return view('levels.create', ['staffOptions' => $this->staffOptions()]);
    }

    public function edit(Level $level): View
    {
        $this->authorizeLevel($level, 'edit');
        $level->loadCount('allocations');
        return view('levels.edit', ['level' => $level, 'staffOptions' => $this->staffOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->hasAllocationPermission('add'), 403);
        $data = $this->validatedData($request);
        DB::transaction(function () use ($data) {
            Staff::whereKey($data['staff_id'])->lockForUpdate()->firstOrFail();
            $this->rejectDuplicate($data);
            Level::create($data);
        });
        return redirect()->route('levels.index')->with('success', 'Level created successfully.');
    }

    public function update(Request $request, Level $level): RedirectResponse
    {
        $this->authorizeLevel($level, 'edit');
        $data = $this->validatedData($request);
        DB::transaction(function () use ($data, $level) {
            Staff::whereIn('id', array_unique([(int) $level->staff_id, (int) $data['staff_id']]))
                ->orderBy('id')->lockForUpdate()->get(['id']);
            $locked = Level::whereKey($level->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeLevel($locked, 'edit');
            if ($locked->allocations()->exists()
                && (trim((string) $locked->level_code) !== $data['level_code'] || (int) $locked->staff_id !== (int) $data['staff_id'])) {
                throw ValidationException::withMessages([
                    'level_code' => 'This level is used by allocations. Its code and Staff / Office cannot be changed. You may update its description or create a separate level.',
                ]);
            }
            $this->rejectDuplicate($data, (int) $locked->id);
            $locked->update($data);
        });
        return redirect()->route('levels.index')->with('success', 'Level updated successfully.');
    }

    public function destroy(Level $level): RedirectResponse
    {
        $this->authorizeLevel($level, 'delete');
        $deleted = DB::transaction(function () use ($level) {
            Staff::whereKey($level->staff_id)->lockForUpdate()->firstOrFail();
            $locked = Level::whereKey($level->getKey())->lockForUpdate()->firstOrFail();
            $this->authorizeLevel($locked, 'delete');
            if ($locked->allocations()->exists()) {
                return false;
            }
            $locked->delete();
            return true;
        });
        return redirect()->route('levels.index')->with($deleted ? 'success' : 'error', $deleted
            ? 'Level deleted successfully.'
            : 'This level cannot be deleted because it is already used by an allocation.');
    }

    private function validatedData(Request $request): array
    {
        if (! auth()->user()->isAdministrator()) {
            abort_unless(auth()->user()->staff_id !== null, 403);
            $request->merge(['staff_id' => auth()->user()->staff_id]);
        }
        foreach (['level_code', 'level_description'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => trim($request->input($field))]);
            }
        }
        return $request->validate([
            'level_code' => ['required', 'string', 'max:50'],
            'level_description' => ['required', 'string', 'max:150'],
            'staff_id' => ['required', 'integer', 'exists:staffs,id'],
        ], ['staff_id.required' => 'Please select a Staff / Office.', 'staff_id.exists' => 'Please select an existing Staff / Office.']);
    }

    private function rejectDuplicate(array $data, ?int $ignoreId = null): void
    {
        $query = Level::where('staff_id', $data['staff_id'])
            ->whereRaw('LOWER(TRIM(level_code)) = ?', [mb_strtolower($data['level_code'])]);
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['level_code' => 'This Level Code already exists for the selected Staff / Office.']);
        }
    }
}
