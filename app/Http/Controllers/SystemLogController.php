<?php

namespace App\Http\Controllers;

use App\Models\Allotment;
use App\Models\SystemLog;
use App\Models\Acct_order_payment;
use App\Models\Cash_official_receipt;
use App\Models\Cash_deposit;
use App\Models\Cash_rcd_list;
use App\Models\Acct_jev;
use App\Models\Comment;
use App\Models\SubmissionAttachment;
use App\Models\SubmissionAttachmentRemarks;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemLogController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', SystemLog::class);
    return view('systemlogs.index');
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
  public function show(SystemLog $systemLog)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(SystemLog $systemLog)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, SystemLog $systemLog)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(SystemLog $systemLog)
  {
    //
  }

  public function getsystemlogs()
  {
    $this->authorize('viewAny', SystemLog::class);
    DB::statement("SET SQL_MODE=''");
    $systemlogs = SystemLog::select([
      'system_logs.id',
      'system_logs.name',
      'system_logs.activity',
      'system_logs.ipaddress',
      'system_logs.created_at',
    ])
      ->groupBy('system_logs.id');

    return DataTables::of($systemlogs)
      ->editColumn('created_at', function (SystemLog $systemlog) {
        return $systemlog->created_at->format('Y-m-d H:i:s');
      })
      ->addColumn('actions', function (SystemLog $systemlog) {
        $history = '';
        if (auth()->user()->can('showhistory', SystemLog::class)) {
          if ($systemlog->history()->exists()) {
            $history = '<a data-bs-toggle="tooltip" data-bs-original-title="History" class="border-0 bg-transparent px-1" href="#" onclick="showHistory(' . $systemlog->id . ')"><i class="fa fa-info-circle text-info"></i></a>';
          }
        }
        return '<div class="btn-group" role="group">' . $history . '</div>';
      })
      ->rawColumns(['actions'])
      ->toJson();
  }

  function getModelFromTable($tableName)
  {
    $modelNamespace = 'App\\Models\\';
    $modelPath = app_path('Models');

    $files = File::files($modelPath);

    foreach ($files as $file) {
      $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
      $fullClass = $modelNamespace . $className;

      if (class_exists($fullClass)) {
        $model = new $fullClass();
        if ($model->getTable() === $tableName) {
          return $fullClass;
        }
      }
    }
    return null;
  }

  public function getmodellogs($tablename, $rowid)
  {
    $class = $this->getModelFromTable($tablename);
    $row =  $class::where('id', $rowid)->first();
    $this->authorize('view', [$class, $row]);

    DB::statement("SET SQL_MODE=''");
    $systemlogs = SystemLog::select([
      'system_logs.id',
      'system_logs.user_id',
      'system_logs.name',
      'system_logs.activity',
      'system_logs.ipaddress',
      'system_logs.created_at',
    ])
      ->where('reference_table', $tablename)
      ->where('reference_id', $rowid)
      ->with(['user:id,firstname,lastname,middlename'])
      ->groupBy('system_logs.id');

    return DataTables::of($systemlogs)
      ->editColumn('created_at', function (SystemLog $systemlog) {
        return $systemlog->created_at->format('Y-m-d H:i:s');
      })
      ->editColumn('activity', function (SystemLog $systemlog) {
        if (strpos($systemlog->activity, ':') !== false) {
          return explode(':', $systemlog->activity)[0];
        } else {
          return $systemlog->activity;
        }
      })
      ->addColumn('full_name', function (SystemLog $systemlog) {
        return $systemlog->user->full_name;
      })
      ->addColumn('actions', function (SystemLog $systemlog) {
        $history = '';
        if (auth()->user()->can('showhistory', SystemLog::class)) {
          if ($systemlog->history()->exists()) {
            $history = '<a data-bs-toggle="tooltip" data-bs-original-title="History" class="border-0 bg-transparent px-1" href="#" onclick="showHistory(' . $systemlog->id . ')"><i class="fa fa-info-circle text-info"></i></a>';
          }
        }
        return '<div class="btn-group" role="group">' . $history . '</div>';
      })
      ->rawColumns(['actions'])
      ->toJson();
  }

  public function gethistory(Request $request)
  {
    $systemlog = SystemLog::find($request->systemlog_id);
    if ($systemlog == null) {
      return response()->json(['error' => 'Unknown Record'], Response::HTTP_UNPROCESSABLE_ENTITY);
    } else {
      if (!auth()->user()->can('showhistory', [SystemLog::class, $systemlog])) {
        return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNPROCESSABLE_ENTITY);
      } else {
        if (!$systemlog->history()->exists()) {
          return response()->json(['error' => 'No Details Found'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
          $content = optional($systemlog->history)->body;
          return response()->json(['data' => $content], Response::HTTP_OK);
        }
      }
    }
  }
}
