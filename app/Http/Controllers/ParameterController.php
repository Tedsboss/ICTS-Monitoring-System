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

    public function index()
    {
        $this->authorize('viewAny', Parameter::class);

        return view('parameters.index');
    }

    public function create()
    {
        // Parameters are edited from the directory.
    }

    public function store(Request $request)
    {
        // Parameter creation is not part of this screen.
    }

    public function show(Parameter $parameter)
    {
        // Parameters are displayed in the directory.
    }

    public function edit(Parameter $parameter)
    {
        $this->authorize('update', [Parameter::class, $parameter]);

        return redirect()->route('parameters.index');
    }

    public function update(ParameterRequest $request, Parameter $parameter)
    {
        $this->authorize('update', [Parameter::class, $parameter]);

        $this->storeAllOldRelationshipValues($parameter);

        DB::transaction(function () use ($request, &$parameter) {
            $parameter = Parameter::whereKey($parameter->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $parameter->name = $request->input('name');
            $parameter->type = $request->input('type');
            $parameter->title = $request->input('title');
            $parameter->description = $request->input('description');
            $parameter->value = $request->input('value');

            if ($parameter->with_duration === 'Y') {
                $parameter->start_date = Carbon::parse($request->input('start_date'))
                    ->format('Y-m-d H:i:s');
                $parameter->end_date = Carbon::parse($request->input('end_date'))
                    ->format('Y-m-d H:i:s');
            }

            $parameter->updated_by = auth()->id();
            $parameter->updated_at = now();
            $parameter->save();
        });

        return redirect()->route('parameters.index')
            ->with('succes', 'Parameter successfully updated');
    }

    public function destroy(Parameter $parameter)
    {
        // Parameter deletion is not part of this screen.
    }

    public function getparameters(Request $request)
    {
        $this->authorize('viewAny', Parameter::class);

        $parameters = Parameter::query()
            ->select([
                'parameters.id', 'parameters.name', 'parameters.type',
                'parameters.title', 'parameters.category', 'parameters.description',
                'parameters.value', 'parameters.with_duration', 'parameters.start_date',
                'parameters.end_date', 'parameters.updated_by', 'parameters.updated_at',
            ])
            ->with('editor:id,firstname,lastname,middlename');

        return DataTables::of($parameters)
            ->editColumn('updated_at', fn (Parameter $parameter) =>
                $parameter->updated_at ? $parameter->updated_at->format('Y-m-d H:i:s') : null
            )
            ->addColumn('editor', fn (Parameter $parameter) => optional($parameter->editor)->full_name)
            ->filterColumn('editor', function ($query, $keyword) {
                $query->whereHas('editor', function ($query) use ($keyword) {
                    $query->whereRaw(
                        "CONCAT_WS(' ', firstname, middlename, lastname) like ?",
                        ["%{$keyword}%"]
                    );
                });
            })
            ->addColumn('actions', function (Parameter $parameter) {
                if (! auth()->user()->can('update', [Parameter::class, $parameter])) {
                    return '<span aria-label="No actions available">—</span>';
                }

                $flags = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                    | JSON_HEX_TAG | JSON_THROW_ON_ERROR;
                $payload = json_encode([
                    'id' => $parameter->id,
                    'name' => $parameter->name,
                    'type' => $parameter->type,
                    'title' => $parameter->title,
                    'category' => $parameter->category,
                    'description' => $parameter->description,
                    'value' => $parameter->value,
                    'with_duration' => $parameter->with_duration,
                    'start_date' => $parameter->start_date
                        ? Carbon::parse($parameter->start_date)->format('Y-m-d\TH:i:s')
                        : null,
                    'end_date' => $parameter->end_date
                        ? Carbon::parse($parameter->end_date)->format('Y-m-d\TH:i:s')
                        : null,
                ], $flags);
                $url = json_encode(route('parameters.update', $parameter->id), $flags);
                $handler = 'showParameter(' . $payload . ', ' . $url . ')';

                return '<div class="btn-group" role="group" aria-label="Parameter actions">'
                    . '<button type="button" class="parameter-action-edit" title="Edit parameter" aria-label="'
                    . e('Edit ' . $parameter->name) . '" onclick="' . e($handler) . '">'
                    . '<i class="fa fa-pencil" aria-hidden="true"></i></button></div>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }
}
