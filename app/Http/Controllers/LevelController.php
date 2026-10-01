<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LevelController extends Controller
{
    private function hasAllocationPermission(string $permission): bool
    {
        $user = auth()->user();

        if ($user->isAdministrator()) {
            return true;
        }

        return $user->role
            && $user->role->permissions->contains(function ($permissionModel) use ($permission) {
                return strcasecmp(
                    (string) optional($permissionModel->module)->name,
                    'Allocation Management'
                ) === 0
                && strcasecmp(
                    (string) $permissionModel->name,
                    $permission
                ) === 0;
            });
    }

    private function scopedLevels()
    {
        $query = Level::query();

        if (! auth()->user()->isAdministrator()) {
            $staffId = auth()->user()->staff_id;

            if ($staffId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('staff_id', (int) $staffId);
            }
        }

        return $query;
    }

    private function authorizeLevel(
        Level $level,
        string $permission
    ): void {
        abort_unless(
            $this->hasAllocationPermission($permission),
            403
        );

        if (auth()->user()->isAdministrator()) {
            return;
        }

        $staffId = auth()->user()->staff_id;

        abort_unless(
            $staffId !== null
            && (int) $level->staff_id === (int) $staffId,
            403
        );
    }

    private function staffOptions()
    {
        return auth()->user()->isAdministrator()
            ? Staff::query()
                ->orderBy('name')
                ->get(['id', 'name', 'abbreviation'])
            : Staff::query()
                ->where('id', auth()->user()->staff_id)
                ->get(['id', 'name', 'abbreviation']);
    }

    public function index(): View
    {
        abort_unless(
            $this->hasAllocationPermission('view'),
            403
        );

        $levels = $this->scopedLevels()
            ->withCount('allocations')
            ->orderBy('level_code')
            ->get();

        return view('levels.index', compact('levels'));
    }

    public function create(): View
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );

        $staffOptions = $this->staffOptions();

        return view(
            'levels.create',
            compact('staffOptions')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            $this->hasAllocationPermission('add'),
            403
        );

        $validated = $request->validate([
            'level_code' => [
                'required',
                'string',
                'max:50',
            ],
            'level_description' => [
                'required',
                'string',
                'max:150',
            ],
            'staff_id' => [
                'nullable',
                'integer',
                'exists:staffs,id',
            ],
        ]);

        $staffId = auth()->user()->isAdministrator()
            ? $validated['staff_id'] ?? null
            : auth()->user()->staff_id;

        if ($staffId === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Please select a Staff / Office.',
                ]);
        }

        $staffId = (int) $staffId;

        if (! auth()->user()->isAdministrator()) {
            abort_unless(
                $staffId === (int) auth()->user()->staff_id,
                403
            );
        }

        $duplicateExists = Level::query()
            ->where('level_code', $validated['level_code'])
            ->where('staff_id', $staffId)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'level_code' => 'This Level Code already exists for the selected Staff / Office.',
                ]);
        }

        Level::create([
            'level_code' => $validated['level_code'],
            'level_description' => $validated['level_description'],
            'staff_id' => $staffId,
        ]);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Level created successfully.');
    }

    public function edit(Level $level): View
    {
        $this->authorizeLevel($level, 'edit');

        $staffOptions = $this->staffOptions();

        return view(
            'levels.edit',
            compact('level', 'staffOptions')
        );
    }

    public function update(
        Request $request,
        Level $level
    ): RedirectResponse {
        $this->authorizeLevel($level, 'edit');

        $validated = $request->validate([
            'level_code' => [
                'required',
                'string',
                'max:50',
            ],
            'level_description' => [
                'required',
                'string',
                'max:150',
            ],
            'staff_id' => [
                'nullable',
                'integer',
                'exists:staffs,id',
            ],
        ]);

        $staffId = auth()->user()->isAdministrator()
            ? $validated['staff_id'] ?? $level->staff_id
            : auth()->user()->staff_id;

        if ($staffId === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Please select a Staff / Office.',
                ]);
        }

        $staffId = (int) $staffId;

        if (! auth()->user()->isAdministrator()) {
            abort_unless(
                $staffId === (int) auth()->user()->staff_id
                && (int) $level->staff_id === (int) auth()->user()->staff_id,
                403
            );
        }

        $duplicateExists = Level::query()
            ->where('level_code', $validated['level_code'])
            ->where('staff_id', $staffId)
            ->where('id', '!=', $level->id)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'level_code' => 'This Level Code already exists for the selected Staff / Office.',
                ]);
        }

        $level->update([
            'level_code' => $validated['level_code'],
            'level_description' => $validated['level_description'],
            'staff_id' => $staffId,
        ]);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Level updated successfully.');
    }

    public function destroy(Level $level): RedirectResponse
    {
        $this->authorizeLevel($level, 'delete');

        if ($level->allocations()->exists()) {
            return redirect()
                ->route('levels.index')
                ->with(
                    'error',
                    'This level cannot be deleted because it is already used by an allocation.'
                );
        }

        $level->delete();

        return redirect()
            ->route('levels.index')
            ->with('success', 'Level deleted successfully.');
    }
}
