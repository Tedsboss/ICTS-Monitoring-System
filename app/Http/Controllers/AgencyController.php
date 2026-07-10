<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgencyRequest;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class AgencyController extends Controller
{
  public function index()
  {
    $this->authorize('viewAny', Agency::class);

    return view('agencies.index');
  }

  public function update(AgencyRequest $request, Agency $agency)
  {
    $this->authorize('update', [Agency::class, $agency]);

    DB::transaction(function () use ($request, &$agency) {
      $agency = Agency::where('id', $agency->id)->lockForUpdate()->first();
      $agency->UACS_AGY_DSC = $request->UACS_AGY_DSC;
      $agency->active = $request->active;
      $agency->head_lname = $request->head_lname;
      $agency->head_mname = $request->head_mname;
      $agency->head_fname = $request->head_fname;
      $agency->head_designation = $request->head_designation;
      $agency->head_telnumber = $request->head_telnumber;
      $agency->head_email = $request->head_email;
      $agency->save();
    });

    return redirect()->route('agencies.index')->with('succes', 'Agency succesfully updated');
  }

  public function getagencies(Request $request)
  {
    $this->authorize('viewAny', Agency::class);

    DB::statement("SET SQL_MODE=''");
    $agencies = Agency::select([
      'agencies.id',
      'agencies.UACS_AGY_ID',
      'agencies.UACS_AGY_DSC',
      'agencies.Abbreviation',
      'agencies.active',
      'agencies.head_lname',
      'agencies.head_mname',
      'agencies.head_fname',
      'agencies.head_designation',
      'agencies.head_telnumber',
      'agencies.head_email',
      'agencies.updated_at',
    ])->groupBy('agencies.id');

    return DataTables::of($agencies)
      ->editColumn('active', function (Agency $agency) {
        if ($agency->active === null) {
          return ['text' => 'Unknown', 'html' => '<span class="badge bg-secondary">Unknown</span>'];
        }

        return $agency->active == 1
          ? ['text' => 'Active', 'html' => '<span class="badge bg-success">Active</span>']
          : ['text' => 'Inactive', 'html' => '<span class="badge bg-secondary">Inactive</span>'];
      })
      ->addColumn('head_name', function (Agency $agency) {
        return trim(collect([$agency->head_fname, $agency->head_mname, $agency->head_lname])->filter()->implode(' '));
      })
      ->addColumn('actions', function (Agency $agency) {
        $edit = '';
        if (auth()->user()->can('update', [Agency::class, $agency])) {
          $edit = '<a data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent px-1" onclick=\'showAgency(' . json_encode($agency, JSON_HEX_APOS) . ', "' . route('agencies.update', $agency->id) . '")\'><i class="fa fa-pencil text-info"></i></a>';
          $edit .= '<a href="' . route('agencies.switchstatus', $agency->id) . '" class="px-1" data-bs-toggle="tooltip" data-bs-original-title="' . ($agency->active == 1 ? 'Active' : 'Inactive') . '"><i class="fa ' . ($agency->active == 1 ? 'fa-unlock text-success' : 'fa-lock text-secondary') . '"></i></a>';
        }

        return '<div class="btn-group" role="group">' . $edit . '</div>';
      })
      ->rawColumns(['active.html', 'actions'])
      ->toJson();
  }

  public function switchstatus(Agency $agency)
  {
    $this->authorize('update', [Agency::class, $agency]);

    $agency->active = $agency->active == 1 ? 0 : 1;
    $agency->save();

    return redirect()->route('agencies.index')->with('succes', 'Agency status has been updated');
  }
}
