<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Module;
use App\Http\Requests\RoleRequest;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
  use TracksHistoryTrait;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', Role::class);
    return view('roles.index');
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
  public function store(RoleRequest $request)
  {
    $this->authorize('create', Role::class);
    $role = new Role;
    $role->name = $request->name;
    $role->description = $request->description;
    $role->created_by = auth()->id();
    $role->save();

    return redirect()->route('roles.index')->with('succes', 'Role succesfully saved');
  }

  /**
   * Display the specified resource.
   */
  public function show(Role $role)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Role $role)
  {
    $this->authorize('edit', [Role::class, $role]);
    $modules = Module::with('permissions')->orderBy('category')->orderBy('administrator', 'desc')->get();
    $categories = Module::select('category')->distinct()->get();

    $role_permissions = $role->permissions->pluck('id')->toArray();

    return view('roles.edit', compact('role', 'modules', 'role_permissions', 'categories'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(RoleRequest $request, Role $role)
  {
    $this->authorize('edit', [Role::class, $role]);
    $this->storeAllOldRelationshipValues($role);

    DB::transaction(function () use ($request, &$role) {
      $role = Role::where('id', $role->id)->lockForUpdate()->first();
      $role->name = $request->name;
      $role->description = $request->description;

      $role->permissions()->sync($request->permissions);
      $role->updated_at = Carbon::now();
      $role->save();
    });

    return redirect()->route('roles.index')->with('succes', 'Role succesfully updated');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Role $role)
  {
    $this->authorize('delete', [Role::class, $role]);

    $role->permissions()->detach();

    $role->delete();
    return redirect()->route('roles.index')->with('succes', 'Role succesfully updated');
  }

  public function getroles(Request $request)
  {
    $this->authorize('viewAny', Role::class);
    DB::statement("SET SQL_MODE=''");
    $roles = Role::select([
      'roles.id',
      'roles.name',
      'roles.description',
      'roles.created_by',
      'roles.created_at',
    ])
      ->with([
        'creator:id,firstname,lastname,middlename',
      ])
      ->groupBy('roles.id');

    return DataTables::of($roles)
      ->editColumn('created_at', function (Role $role) {
        return $role->created_at->format('Y-m-d H:i:s');
      })
      ->addColumn('creator', function (Role $role) {
        if ($role->creator == null) {
          return null;
        }
        return $role->creator->full_name;
      })
      ->filterColumn('creator', function ($query, $keyword) {
        $query->whereHas('creator', function ($q) use ($keyword) {
          $q->whereRaw("CONCAT(firstname, ' ', lastname) like ?", "%{$keyword}%");
        });
      })
      ->addColumn('actions', function (Role $role) {
        $delete = '';
        $edit = '';
        if (auth()->user()->can('delete', [Role::class, $role])) {
          $delete = '<form action="' . route('roles.destroy', $role->id) . '" method="post"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button onclick="return confirm(\'Are you sure you want to remove the act?\')" data-bs-toggle="tooltip" data-bs-original-title="Delete" class="border-0 bg-transparent"><i class="fa fa-times text-danger"></i></button></form>';
        }
        if (auth()->user()->can('edit', [Role::class, $role])) {
          $edit = '<a data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent px-1" href="' . route('roles.edit', $role->id) . '"><i class="fa fa-pencil text-info"></i></a>';
        }
        return '<div class="btn-group" role="group">' . $edit . $delete . '</div>';
      })
      ->rawColumns(['actions'])
      ->toJson();
  }
}
