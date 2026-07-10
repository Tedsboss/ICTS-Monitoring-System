<?php

namespace App\Http\Controllers;

use App\Http\Requests\DivisionRequest;
use App\Models\Division;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DivisionController extends Controller
{
  use TracksHistoryTrait;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', Division::class);
    return view('divisions.index');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Division $division)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Division $division)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(DivisionRequest $request, Division $division)
  {
    $this->authorize('update', [Division::class, $division]);
    $this->storeAllOldRelationshipValues($division);

    DB::transaction(function () use ($request, &$division) {
      $division = Division::where('id', $division->id)->lockForUpdate()->first();
      $division->name = $request->name;
      $division->abbreviation = $request->abbreviation;
      $division->head_name = $request->head_name;
      $division->head_position = $request->head_position;
      $division->head_email = $request->head_email;
      $division->can_review = $request->can_review;
      $division->updated_at = Carbon::now();
      $division->save();
    });
    return redirect()->route('divisions.index')->with('succes', 'Division succesfully updated');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Division $division)
  {
    //
  }

  public function getdivisions(Request $request)
  {
    $this->authorize('viewAny', Division::class);
    DB::statement("SET SQL_MODE=''");
    $divisions = Division::select([
      'divisions.id',
      'divisions.name',
      'divisions.abbreviation',
      'divisions.head_name',
      'divisions.head_position',
      'divisions.head_email',
      'divisions.can_review',
      'divisions.staff_id',
      'divisions.updated_at',
    ])
      ->with([
        'staff:id,name,abbreviation',
      ])
      ->groupBy('divisions.id');

    return DataTables::of($divisions)
      ->addColumn('staff', function (Division $division) {
        if ($division->staff == null) {
          return null;
        }
        return $division->staff->abbreviation;
      })
      ->addColumn('actions', function (Division $division) {
        $edit = '';
        if (auth()->user()->can('update', [Division::class, $division])) {
          $edit = '<a data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent px-1" onclick=\'showDivision(' . json_encode($division, JSON_HEX_APOS) . ', "' . route('divisions.update', $division->id) . '")\'><i class="fa fa-pencil text-info"></i></a>';
          $edit .= '<a href="' . route('divisions.switchstatus', $division->id) . '" class="px-1" data-bs-toggle="tooltip" data-bs-original-title="' . ($division->can_review == 'Y' ? 'Enabled' : 'Disabled') . '"><i class="fa ' . ($division->can_review == 'N' ? 'fa-lock text-secondary' : 'fa-unlock text-success') . '"></i></a>';
        }
        return '<div class="btn-group" role="group">' . $edit . '</div>';
      })
      ->rawColumns(['actions'])
      ->toJson();
  }

  public function switchstatus(Division $division)
  {
    $this->authorize('update', [Division::class, $division]);
    $division->can_review  = $division->can_review == 'Y' ? 'N' : 'Y';
    $division->save();

    if ($division->can_review == 'Y') {
      $staff = $division->staff;
      $staff->can_check = 1;
      $staff->save();
    }

    return redirect()->route('divisions.index')->with('succes', 'Division has been updated');
  }
}
