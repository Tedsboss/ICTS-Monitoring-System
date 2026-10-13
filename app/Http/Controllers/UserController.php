<?php
namespace App\Http\Controllers;
use App\Http\Requests\UserRequest;
use App\Models\Agency;
use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Traits\TracksHistoryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
class UserController extends Controller
{
    use TracksHistoryTrait;
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $roles = Role::query()->where('name', '!=', 'Super Admin')->orderBy('name')->get();
        $agencies = auth()->user()->isSuperAdmin()
            ? Agency::orderBy('UACS_AGY_DSC')->get()
            : Agency::where('id', auth()->user()->agency_id)->get();
        $staffs = Staff::query()->orderBy('office_id')->orderBy('name')->get();
        $divisions = Division::query()->orderBy('name')->get();
        $positions = Position::query()->orderBy('name')->get();
        $depDevAgencyIds = Agency::depDevIds();
        return view('users.index', compact('roles', 'agencies', 'staffs', 'divisions', 'positions', 'depDevAgencyIds'));
    }
    public function create()
    {
        $this->authorize('create', User::class);

        return redirect()->route('users.index');
    }
    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);
        $user = new User();
        $user->firstname = $request->firstname;
        $user->middlename = $request->middlename;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->agency_id = $request->agency_id;
        $isDepDev = Agency::isDepDevId($request->agency_id);
        $user->staff_id = $isDepDev ? $request->staff_id : null;
        $user->division_id = $isDepDev ? $request->division_id : null;
        $user->position_id = $request->position_id;
        $user->role_id = $request->role_id;
        $user->enabledark = 'N';
        $user->emailnotif = 'Y';
        $user->twofactor = 'Y';
        $user->twofactortype = 'Email';
        if ($request->filled('new-password')) {
            $user->password = $request->get('new-password');
        }
        $user->first_login = 'Y';
        $user->save();
        return redirect()->route('users.index')->with('succes', 'User successfully saved');
    }
    public function show(string $id)
    {
        $this->authorize('viewAny', User::class);
        User::findOrFail($id);

        return redirect()->route('users.index');
    }
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('edit', [User::class, $user]);

        return redirect()->route('users.index');
    }
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('edit', [User::class, $user]);
        $this->storeAllOldRelationshipValues($user);
        DB::transaction(function () use ($request, &$user) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->agency_id = $request->agency_id;
            $isDepDev = Agency::isDepDevId($request->agency_id);
            $user->staff_id = $isDepDev ? $request->staff_id : null;
            $user->division_id = $isDepDev ? $request->division_id : null;
            $user->position_id = $request->position_id;
            $user->role_id = $request->role_id;
            if ($request->filled('new-password')) {
                $user->password = $request->get('new-password');
            }
            $user->save();
        });
        return redirect()->route('users.index')->with('succes', 'User successfully updated');
    }
    public function destroy(User $user)
    {
        $this->authorize('delete', [User::class, $user]);
        $user->delete();
        return redirect()->route('users.index')->with('succes', 'User successfully deleted');
    }
    public function getusers(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $fullNameExpression = "TRIM(CONCAT_WS(' ', users.firstname, users.middlename, users.lastname))";
        $users = User::query()
            ->select([
                'users.id', 'users.firstname', 'users.middlename', 'users.lastname',
                'users.email', 'users.agency_id', 'users.staff_id', 'users.division_id',
                'users.position_id', 'users.role_id', DB::raw($fullNameExpression . ' as fullname'),
            ])
            ->with([
                'staff:id,name,abbreviation', 'division:id,staff_id,name', 'role:id,name',
                'agency:id,UACS_AGY_DSC,Abbreviation', 'position:id,name',
            ]);
        return DataTables::of($users)
            ->addColumn('designation', fn (User $user) => optional($user->position)->name)
            ->editColumn('staff', fn (User $user) => optional($user->staff)->name)
            ->addColumn('division', fn (User $user) => optional($user->division)->name)
            ->addColumn('agency', fn (User $user) => optional($user->agency)->display_name)
            ->addColumn('role', function (User $user) {
                if ($user->role === null) {
                    return null;
                }
                if (auth()->user()->can('edit', [Role::class, $user->role])) {
                    return '<a class="direk-role-link" data-bs-toggle="tooltip" data-bs-original-title="Update Role" href="'
                        . e(route('roles.edit', $user->role_id)) . '">' . e($user->role->name) . '</a>';
                }
                return e($user->role->name);
            })
            ->addColumn('actions', function (User $user) {
                $actions = '';
                $accountName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: 'user';
                if (auth()->user()->can('edit', [User::class, $user])) {
                    // Only send the fields consumed by the edit modal.
                    $payload = json_encode([
                        'id' => $user->id,
                        'firstname' => $user->firstname,
                        'middlename' => $user->middlename,
                        'lastname' => $user->lastname,
                        'email' => $user->email,
                        'agency_id' => $user->agency_id,
                        'staff_id' => $user->staff_id,
                        'division_id' => $user->division_id,
                        'position_id' => $user->position_id,
                        'role_id' => $user->role_id,
                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG | JSON_THROW_ON_ERROR);
                    $updateUrl = json_encode(route('users.update', $user->id), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG | JSON_THROW_ON_ERROR);
                    $handler = 'showUser(' . $payload . ', ' . $updateUrl . ')';
                    $actions .= '<button type="button" class="border-0 bg-transparent px-2" data-bs-toggle="tooltip" data-bs-original-title="Edit" title="Edit user" aria-label="'
                        . e('Edit ' . $accountName) . '" onclick="' . e($handler) . '"><i class="fa fa-pencil text-info" aria-hidden="true"></i></button>';
                } elseif (auth()->id() == $user->id) {
                    $actions .= '<a class="border-0 bg-transparent px-2" data-bs-toggle="tooltip" data-bs-original-title="My Profile" title="My Profile" aria-label="My Profile" href="'
                        . e(route('user-profile')) . '"><i class="fa fa-user text-success" aria-hidden="true"></i></a>';
                }
                if (auth()->user()->can('delete', [User::class, $user])) {
                    $actions .= '<form action="' . e(route('users.destroy', $user->id)) . '" method="post" class="d-inline">'
                        . '<input type="hidden" name="_method" value="DELETE">'
                        . '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'
                        . '<button type="submit" class="border-0 bg-transparent px-2" data-bs-toggle="tooltip" data-bs-original-title="Delete" title="Delete user" aria-label="'
                        . e('Delete ' . $accountName) . '" onclick="return confirm(\'Are you sure you want to delete this user?\');"><i class="fa fa-times text-danger" aria-hidden="true"></i></button></form>';
                }
                return $actions === '' ? '<span aria-label="No actions available">—</span>' : '<div class="direk-user-actions" role="group" aria-label="User actions">' . $actions . '</div>';
            })
            ->orderColumn('fullname', function ($query, $direction) use ($fullNameExpression) {
                $direction = strtolower((string) $direction) === 'desc' ? 'desc' : 'asc';
                $query->orderByRaw($fullNameExpression . ' ' . $direction);
            })
            ->filterColumn('fullname', function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereRaw("CONCAT_WS(' ', users.firstname, users.lastname) like ?", ["%{$keyword}%"])
                        ->orWhereRaw("CONCAT_WS(' ', users.firstname, users.middlename, users.lastname) like ?", ["%{$keyword}%"])
                        ->orWhereRaw("CONCAT_WS(' ', users.lastname, users.firstname) like ?", ["%{$keyword}%"])
                        ->orWhere('users.firstname', 'like', "%{$keyword}%")
                        ->orWhere('users.middlename', 'like', "%{$keyword}%")
                        ->orWhere('users.lastname', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('staff.name', function ($query, $keyword) {
                $query->whereHas('staff', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")->orWhere('abbreviation', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('division.name', function ($query, $keyword) {
                $query->whereHas('division', fn ($query) => $query->where('name', 'like', "%{$keyword}%"));
            })
            ->filterColumn('agency.UACS_AGY_DSC', function ($query, $keyword) {
                $query->whereHas('agency', function ($query) use ($keyword) {
                    $query->where('UACS_AGY_DSC', 'like', "%{$keyword}%")->orWhere('Abbreviation', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('position.name', function ($query, $keyword) {
                $query->whereHas('position', fn ($query) => $query->where('name', 'like', "%{$keyword}%"));
            })
            ->filterColumn('role.name', function ($query, $keyword) {
                $query->whereHas('role', fn ($query) => $query->where('name', 'like', "%{$keyword}%"));
            })
            ->rawColumns(['role', 'actions'])
            ->toJson();
    }
}
