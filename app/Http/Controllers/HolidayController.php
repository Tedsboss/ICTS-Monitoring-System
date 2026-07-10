<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayRequest;
use App\Models\Holiday;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class HolidayController extends Controller
{
  use TracksHistoryTrait;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', Holiday::class);
    return view('holidays.index');
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
  public function store(HolidayRequest $request)
  {
    $this->authorize('create', Holiday::class);
    DB::transaction(function () use ($request, &$holiday) {
      $holiday = new Holiday;
      $holiday->name = $request->name;
      $holiday->type = $request->type;
      $holiday->whole_day = $request->whole_day;
      $holiday->repeat_every_year = $request->repeat_every_year;
      $holiday->start = $request->start;
      $holiday->end = $request->end;
      $holiday->updated_by = auth()->id();
      $holiday->save();
    });
    return redirect()->route('holidays.index')->with('succes', 'Holiday succesfully saved');
  }

  /**
   * Display the specified resource.
   */
  public function show(Holiday $holiday)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Holiday $holiday)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(HolidayRequest $request, Holiday $holiday)
  {
    $this->authorize('update', [Holiday::class, $holiday]);
    $this->storeAllOldRelationshipValues($holiday);

    DB::transaction(function () use ($request, &$holiday) {
      $holiday = Holiday::where('id', $holiday->id)->lockForUpdate()->first();
      $holiday->name = $request->name;
      $holiday->type = $request->type;
      $holiday->whole_day = $request->whole_day;
      $holiday->repeat_every_year = $request->repeat_every_year;
      $holiday->start = Carbon::parse($request->start) . '';
      $holiday->end = Carbon::parse($request->end) . '';
      $holiday->updated_by = auth()->id();
      $holiday->updated_at = Carbon::now();
      $holiday->save();
    });
    return redirect()->route('holidays.index')->with('succes', 'Holiday succesfully updated');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Holiday $holiday)
  {
    $holiday->delete();
    return redirect()->route('holidays.index')->with('succes', 'Holiday succesfully deleted');
  }

  public function getholidays(Request $request)
  {
    $this->authorize('viewAny', Holiday::class);
    DB::statement("SET SQL_MODE=''");
    $holidays = Holiday::select([
      'holidays.id',
      'holidays.name',
      'holidays.type',
      'holidays.start',
      'holidays.end',
      'holidays.whole_day',
      'holidays.repeat_every_year',
      'holidays.updated_by',
      'holidays.updated_at',
    ])
      ->with([
        'editor:id,firstname,lastname,middlename',
      ])
      ->groupBy('holidays.id');

    return DataTables::of($holidays)
      // ->editColumn('start', function (Holiday $holiday) {
      //   return $holiday->start->format('Y-m-d H:i:s');
      // })
      ->editColumn('updated_at', function (Holiday $holiday) {
        return $holiday->updated_at->format('Y-m-d H:i:s');
      })
      ->addColumn('editor', function (Holiday $holiday) {
        if ($holiday->editor == null) {
          return null;
        }
        return $holiday->editor->full_name;
      })
      ->filterColumn('editor', function ($query, $keyword) {
        $query->whereHas('editor', function ($q) use ($keyword) {
          $q->whereRaw("CONCAT(firstname, ' ', lastname) like ?", "%{$keyword}%");
        });
      })
      ->addColumn('actions', function (Holiday $holiday) {
        $delete = '';
        $edit = '';
        if (auth()->user()->can('delete', [Holiday::class, $holiday])) {
          $delete = '<form action="' . route('holidays.destroy', $holiday->id) . '" method="post"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button onclick="return confirm(\'Are you sure you want to delete?\')" data-bs-toggle="tooltip" data-bs-original-title="Delete" class="border-0 bg-transparent"><i class="fa fa-times text-danger"></i></button></form>';
        }
        if (auth()->user()->can('update', [Holiday::class, $holiday])) {
          $edit = '<a data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent px-1" onclick=\'showHoliday(' . json_encode($holiday, JSON_HEX_APOS) . ', "' . route('holidays.update', $holiday->id) . '")\'><i class="fa fa-pencil text-info"></i></a>';
        }
        return '<div class="btn-group" role="group">' . $edit . $delete . '</div>';
      })
      ->rawColumns(['actions'])
      ->toJson();
  }
}
