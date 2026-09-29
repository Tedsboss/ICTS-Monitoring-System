<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LevelController extends Controller
{
    public function index(): View
    {
        $levels = Level::orderBy('level_code')->get();

        return view('levels.index', compact('levels'));
    }

    public function create(): View
    {
        return view('levels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level_code' => ['required', 'string', 'max:50', 'unique:levels,level_code'],
            'level_description' => ['required', 'string', 'max:150'],
        ]);

        Level::create($validated);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Level created successfully.');
    }

    public function edit(Level $level): View
    {
        return view('levels.edit', compact('level'));
    }

    public function update(Request $request, Level $level): RedirectResponse
    {
        $validated = $request->validate([
            'level_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('levels', 'level_code')->ignore($level->id),
            ],
            'level_description' => ['required', 'string', 'max:150'],
        ]);

        $level->update($validated);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Level updated successfully.');
    }

    public function destroy(Level $level): RedirectResponse
    {
        if ($level->allocations()->exists()) {
            return redirect()
                ->route('levels.index')
                ->with('error', 'This level cannot be deleted because it is already used by an allocation.');
        }

        $level->delete();

        return redirect()
            ->route('levels.index')
            ->with('success', 'Level deleted successfully.');
    }
}