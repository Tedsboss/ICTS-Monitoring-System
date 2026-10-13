<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Yajra\DataTables\DataTables;

class SystemLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', SystemLog::class);

        return view('systemlogs.index');
    }

    public function getsystemlogs()
    {
        $this->authorize('viewAny', SystemLog::class);

        $logs = SystemLog::query()->select([
            'system_logs.id', 'system_logs.name', 'system_logs.activity',
            'system_logs.ipaddress', 'system_logs.created_at',
        ])->withExists('history');

        return DataTables::of($logs)
            ->editColumn('created_at', fn (SystemLog $log) => $log->created_at?->format('Y-m-d H:i:s'))
            ->addColumn('actions', fn (SystemLog $log) => $this->historyAction($log))
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function getModelFromTable($tableName): ?string
    {
        foreach (File::files(app_path('Models')) as $file) {
            $class = 'App\\Models\\' . pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (! $reflection->isInstantiable()
                || ($reflection->getConstructor()?->getNumberOfRequiredParameters() ?? 0) > 0) {
                continue;
            }

            if ((new $class())->getTable() === $tableName) {
                return $class;
            }
        }

        return null;
    }

    public function getmodellogs($tablename, $rowid)
    {
        $class = $this->getModelFromTable($tablename);
        abort_if($class === null, 404);
        $row = $class::whereKey($rowid)->firstOrFail();
        $this->authorize('view', [$class, $row]);

        $logs = SystemLog::query()->select([
            'system_logs.id', 'system_logs.user_id', 'system_logs.name',
            'system_logs.activity', 'system_logs.ipaddress', 'system_logs.created_at',
        ])->where('reference_table', $tablename)
            ->where('reference_id', $rowid)
            ->with('user:id,firstname,lastname,middlename')
            ->withExists('history');

        return DataTables::of($logs)
            ->editColumn('created_at', fn (SystemLog $log) => $log->created_at?->format('Y-m-d H:i:s'))
            ->editColumn('activity', fn (SystemLog $log) => explode(':', (string) $log->activity, 2)[0])
            ->addColumn('full_name', fn (SystemLog $log) => $log->user?->full_name ?: $log->name)
            ->addColumn('actions', fn (SystemLog $log) => $this->historyAction($log))
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function gethistory(Request $request)
    {
        $data = $request->validate(['systemlog_id' => ['required', 'integer', 'min:1']]);
        $log = SystemLog::find($data['systemlog_id']);

        if (! $log) {
            return response()->json(['error' => 'Unknown Record'], 404);
        }

        if (! $request->user()?->can('showhistory', [SystemLog::class, $log])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history = $log->history;

        if (! $history) {
            return response()->json(['error' => 'No Details Found'], 422);
        }

        return response()->json(['data' => $history->body]);
    }

    private function historyAction(SystemLog $log): string
    {
        if (! $log->history_exists
            || ! auth()->user()?->can('showhistory', [SystemLog::class, $log])) {
            return '';
        }

        return '<button type="button" class="log-history-button" aria-label="View change details" '
            . 'title="View change details" onclick="showHistory(' . (int) $log->id . ')">'
            . '<i class="fa fa-history" aria-hidden="true"></i><span>Details</span></button>';
    }
}
