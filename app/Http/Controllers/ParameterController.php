<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParameterRequest;
use App\Models\Parameter;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ParameterController extends Controller
{
  use TracksHistoryTrait;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', Parameter::class);
    return view('parameters.index');
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
  public function show(Parameter $parameter)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Parameter $parameter)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(ParameterRequest $request, Parameter $parameter)
  {
    $this->authorize('update', [Parameter::class, $parameter]);
    $this->storeAllOldRelationshipValues($parameter);

    DB::transaction(function () use ($request, &$parameter) {
      $parameter = Parameter::where('id', $parameter->id)->lockForUpdate()->first();
      $parameter->name = $request->name;
      $parameter->type = $request->type;
      $parameter->title = $request->title;
      $parameter->description = $request->description;
      $parameter->value = $request->value;
      if ($parameter->with_duration == 'Y') {
        $parameter->start_date = Carbon::parse($request->start_date) . '';
        $parameter->end_date = Carbon::parse($request->end_date) . '';
      }

      $parameter->updated_by = auth()->id();
      $parameter->updated_at = Carbon::now();
      $parameter->save();
    });

    return redirect()->route('parameters.index')->with('succes', 'Parameter succesfully updated');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Parameter $parameter)
  {
    //
  }

  public function getparameters(Request $request)
  {
    $this->authorize('viewAny', Parameter::class);
    DB::statement("SET SQL_MODE=''");
    $parameters = Parameter::select([
      'parameters.id',
      'parameters.name',
      'parameters.type',
      'parameters.title',
      'parameters.category',
      'parameters.description',
      'parameters.value',
      'parameters.with_duration',
      'parameters.start_date',
      'parameters.end_date',
      'parameters.updated_by',
      'parameters.updated_at',
    ])
      ->with([
        'editor:id,firstname,lastname,middlename',
      ])
      ->groupBy('parameters.id');

    return DataTables::of($parameters)
      ->editColumn('updated_at', function (Parameter $parameter) {
        return $parameter->updated_at->format('Y-m-d H:i:s');
      })
      ->addColumn('editor', function (Parameter $parameter) {
        if ($parameter->editor == null) {
          return null;
        }
        return $parameter->editor->full_name;
      })
      ->filterColumn('editor', function ($query, $keyword) {
        $query->whereHas('editor', function ($q) use ($keyword) {
          $q->whereRaw("CONCAT(firstname, ' ', lastname) like ?", "%{$keyword}%");
        });
      })
      ->addColumn('actions', function (Parameter $parameter) {
        if (auth()->user()->can('update', [Parameter::class, $parameter])) {
          return '<div class="btn-group" role="group"><button data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent" onclick=\'showParameter(' . json_encode($parameter, JSON_HEX_APOS) . ', "' . route('parameters.update', $parameter->id) . '")\'><i class="fa fa-pencil text-info"></i></button></div>';
        }
      })
      ->rawColumns(['actions'])
      ->toJson();
  }
}
