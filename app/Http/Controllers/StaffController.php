<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Requests\StaffToggleRequest;
use App\Models\Staff;
use App\Policies\StaffPolicy;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StaffController extends Controller
{
  use TracksHistoryTrait;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', Staff::class);
    return view('staffs.index');
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
  public function store(StaffRequest $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Staff $staff)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Staff $staff)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(StaffRequest $request, Staff $staff)
  {
    $this->authorize('update', [Staff::class, $staff]);
    $this->storeAllOldRelationshipValues($staff);

    DB::transaction(function () use ($request, &$staff) {
      $staff = Staff::where('id', $staff->id)->lockForUpdate()->first();
      $staff->name = $request->name;
      $staff->abbreviation = $request->abbreviation;
      $staff->head_name = $request->head_name;
      $staff->head_position = $request->head_position;
      $staff->head_email = $request->head_email;
      // $staff->can_check = $request->can_check;
      $staff->updated_at = Carbon::now();
      $staff->save();
    });
    return redirect()->route('staffs.index')->with('succes', 'Staff succesfully updated');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Staff $staff)
  {
    //
  }

  public function getstaffs()
  {
    $this->authorize('viewAny', Staff::class);
    DB::statement("SET SQL_MODE=''");
    $staffs = Staff::select([
      'id',
      'name',
      'abbreviation',
      'head_name',
      'head_position',
      'head_email',
      'can_check',
      'office_id',
      // 'region_code'
    ])
      ->where(function ($query) {
        $query->where('is_DCO', 'Y')
          ->orWhere(function ($q) {
            $q->where('is_DCO', 'N')
              ->whereNotNull('region_code');
          });
      });

    return DataTables::of($staffs)
      ->addColumn('actions', function (Staff $staff) {
        $edit = '';
        if (auth()->user()->can('update', [Staff::class, $staff])) {
          $edit = '<a data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent px-1" onclick=\'showStaff(' . json_encode($staff, JSON_HEX_APOS) . ', "' . route('staffs.update', $staff->id) . '")\'><i class="fa fa-pencil text-info"></i></a>';
          $edit .= '<a href="' . route('staffs.switchstatus', $staff->id) . '" class="px-1" data-bs-toggle="tooltip" data-bs-original-title="' . ($staff->can_check == 1 ? 'Enabled' : 'Disabled') . '"><i class="fa ' . ($staff->can_check == 0 ? 'fa-lock text-secondary' : 'fa-unlock text-success') . '"></i></a>';
        }
        return '<div class="btn-group" role="group">' . $edit . '</div>';
      })
      ->rawColumns(['actions'])
      ->toJson();
  }

  public function switchstatus(Staff $staff)
  {
    $this->authorize('update', [Staff::class, $staff]);
    $staff->can_check  = $staff->can_check == 1 ? 0 : 1;
    $staff->save();

    if ($staff->can_check == 0) {
      foreach ($staff->divisions as $division) {
        $division->can_review = 'N';
        $division->save();
      }
    }
    return redirect()->route('staffs.index')->with('succes', 'Staff has been updated');
  }
}
