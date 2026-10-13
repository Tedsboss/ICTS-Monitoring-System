<?php
namespace App\Http\Controllers;
use App\Http\Requests\RoleRequest;
use App\Models\Module;
use App\Models\Role;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
class RoleController extends Controller
{
    use TracksHistoryTrait;
    private const DIREK_MODULES = [
        // Administration
        'User Management',
        'Roles and Permissions',
        'Staff Management',
        'Division Management',
        'System Parameters',
        'System Logs',
        // Financial Management
        'Allocation Management',
        'Allocation Type Management',
        'Financial Plan',
        'Work Plan',
        'Procurement',
        'SAEB',
    ];
    public function index()
    {
        $this->authorize('viewAny', Role::class);
        return view('roles.index');
    }
    public function create()
    {
        //
    }
    public function store(RoleRequest $request)
    {
        $this->authorize('create', Role::class);
        $role = new Role;
        $role->name = $request->name;
        $role->description = $request->description;
        $role->created_by = auth()->id();
        $role->save();
        return redirect()
            ->route('roles.index')
            ->with('succes', 'Role successfully saved');
    }
    public function show(Role $role)
    {
        //
    }
    public function edit(Role $role)
    {
        $this->authorize('edit', [Role::class, $role]);
        $modules = Module::with('permissions')
            ->whereIn('name', self::DIREK_MODULES)
            ->orderBy('administrator', 'desc')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $categories = $modules
            ->map(function ($module) {
                return (object) [
                    'category' => $module->category,
                ];
            })
            ->unique('category')
            ->values();
        $role_permissions = $role
            ->permissions
            ->pluck('id')
            ->toArray();
        return view(
            'roles.edit',
            compact(
                'role',
                'modules',
                'role_permissions',
                'categories'
            )
        );
    }
    public function update(RoleRequest $request, Role $role)
    {
        $this->authorize('edit', [Role::class, $role]);
        $this->storeAllOldRelationshipValues($role);
        DB::transaction(function () use ($request, &$role) {
            $role = Role::where('id', $role->id)
                ->lockForUpdate()
                ->firstOrFail();
            $direkModuleIds = Module::whereIn(
                'name',
                self::DIREK_MODULES
            )
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();
            $existingLegacyPermissionIds = $role
                ->permissions()
                ->whereNotIn('module_id', $direkModuleIds)
                ->pluck('permissions.id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();
            $requestedPermissionIds = collect(
                $request->input('permissions', [])
            )
                ->map(function ($id) {
                    return (int) $id;
                })
                ->filter()
                ->unique()
                ->values();
            $validDirekPermissionIds = DB::table('permissions')
                ->whereIn('module_id', $direkModuleIds)
                ->whereIn(
                    'id',
                    $requestedPermissionIds->toArray()
                )
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();
            $finalPermissionIds = collect(
                $existingLegacyPermissionIds
            )
                ->merge($validDirekPermissionIds)
                ->unique()
                ->values()
                ->toArray();
            $role->name = $request->name;
            $role->description = $request->description;
            $role->permissions()->sync(
                $finalPermissionIds
            );
            $role->updated_at = Carbon::now();
            $role->save();
        });
        return redirect()
            ->route('roles.index')
            ->with('succes', 'Role successfully updated');
    }
    public function destroy(Role $role)
    {
        $this->authorize('delete', [Role::class, $role]);
        $usersCount = DB::table('users')
            ->where('role_id', $role->id)
            ->count();
        if ($usersCount > 0) {
            return redirect()
                ->route('roles.index')
                ->with(
                    'error',
                    "Cannot delete role '{$role->name}'. It is currently assigned to {$usersCount} user(s). Reassign those users to another role first."
                );
        }
        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();
        });
        return redirect()
            ->route('roles.index')
            ->with('succes', 'Role successfully deleted');
    }
    public function getroles(Request $request)
    {
        $this->authorize('viewAny', Role::class);
        $roles = Role::select([
            'roles.id',
            'roles.name',
            'roles.description',
            'roles.created_by',
            'roles.created_at',
        ])
            ->with([
                'creator:id,firstname,lastname,middlename',
            ]);
        return DataTables::of($roles)
            ->editColumn('created_at', function (Role $role) {
                return $role->created_at
                    ? $role->created_at->format('Y-m-d H:i:s')
                    : null;
            })
            ->addColumn('creator', function (Role $role) {
                if ($role->creator == null) {
                    return null;
                }
                return $role->creator->full_name;
            })
            ->filterColumn('creator', function ($query, $keyword) {
                $query->whereHas('creator', function ($q) use ($keyword) {
                    $q->whereRaw(
                        "CONCAT(firstname, ' ', lastname) like ?",
                        ["%{$keyword}%"]
                    );
                });
            })
            ->addColumn('actions', function (Role $role) {
                $delete = '';
                $edit = '';
                if (
                    auth()->user()->can(
                        'delete',
                        [Role::class, $role]
                    )
                ) {
                    $delete =
                        '<form action="' .
                        e(route('roles.destroy', $role->id)) .
                        '" method="post">' .
                        '<input type="hidden" name="_method" value="DELETE">' .
                        '<input type="hidden" name="_token" value="' .
                        e(csrf_token()) .
                        '">' .
                        '<button
                            type="submit" aria-label="Delete role" title="Delete role" onclick="return confirm(\'Are you sure you want to remove the role?\')"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="Delete"
                            class="border-0 bg-transparent"
                        >
                            <i class="fa fa-times text-danger" aria-hidden="true"></i>
                        </button>' .
                        '</form>';
                }
                if (
                    auth()->user()->can(
                        'edit',
                        [Role::class, $role]
                    )
                ) {
                    $edit =
                        '<a
                            data-bs-toggle="tooltip"
                            data-bs-original-title="Edit" aria-label="Edit role permissions" title="Edit role permissions"
                            class="border-0 bg-transparent px-1"
                            href="' .
                        e(route('roles.edit', $role->id)) .
                        '"
                        >
                            <i class="fa fa-pencil text-info" aria-hidden="true"></i>
                        </a>';
                }
                return
                    '<div class="btn-group" role="group">' .
                    $edit .
                    $delete .
                    '</div>';
            })
            ->rawColumns([
                'actions',
            ])
            ->toJson();
    }
}
