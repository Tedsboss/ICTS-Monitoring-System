<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\Agency;
use App\Models\User;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Division;
use App\Models\Unit;
use App\Models\Position;
use App\Models\OfficeLocation;
use App\Traits\TracksHistoryTrait;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
  use TracksHistoryTrait;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', User::class);
    $roles = Role::all();
    $agencies = auth()->user()->isSuperAdmin()
      ? Agency::orderBy('UACS_AGY_DSC')->get()
      : Agency::where('id', auth()->user()->agency_id)->get();
    $staffs = Staff::orderBy('office_id', 'asc')->orderBy('name', 'asc')->get();
    $divisions = Division::all();
    $positions = Position::all();
    $depDevAgencyIds = Agency::depDevIds();
    return view('users.index', compact('roles', 'agencies', 'staffs', 'divisions', 'positions', 'depDevAgencyIds'));
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
  public function store(UserRequest $request)
  {
    $this->authorize('create', User::class);
    $user = new User;
    $user->firstname = $request->firstname;
    $user->middlename = $request->middlename;
    $user->lastname = $request->lastname;
    $user->email = $request->email;
    $user->gender = $request->gender;
    $user->birthday = $request->birthday;
    $user->agency_id = $request->agency_id;
    $user->staff_id = Agency::isDepDevId($request->agency_id) ? $request->staff_id : null;
    $user->division_id = Agency::isDepDevId($request->agency_id) ? $request->division_id : null;
    $user->position_id = $request->position_id;
    $user->location = $request->location;
    $user->phone = $request->phone;
    $user->role_id = $request->role_id;

    $user->enabledark = $request->enabledark ?? 'N';
    $user->emailnotif = $request->emailnotif ?? 'Y';
    $user->twofactor = $request->twofactor ?? 'Y';

    if ($request->get('new-password') == '' || $request->get('new-password') == null) {
    } else {
      $user->password = $request->get('new-password');
    }
    if ($request->file('avatar')) {
      $user->avatar = $request->file('avatar')->store('/', 'avatars');
    }

    $user->first_login = 'Y';
    $user->save();
    return redirect()->route('users.index')->with('succes', 'User succesfully saved');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UserRequest $request, User $user)
  {
    $this->authorize('edit', [User::class, $user]);
    $this->storeAllOldRelationshipValues($user);

    DB::transaction(function () use ($request, &$user) {
      $user = User::where('id', $user->id)->lockForUpdate()->first();
      $user->firstname = $request->firstname;
      $user->middlename = $request->middlename;
      $user->lastname = $request->lastname;
      $user->email = $request->email;
      $user->gender = $request->gender;
      $user->birthday = $request->birthday;
      $user->agency_id = $request->agency_id;
      $user->staff_id = Agency::isDepDevId($request->agency_id) ? $request->staff_id : null;
      $user->division_id = Agency::isDepDevId($request->agency_id) ? $request->division_id : null;
      $user->position_id = $request->position_id;
      $user->location = $request->location;
      $user->phone = $request->phone;
      $user->role_id = $request->role_id;
      $user->enabledark = $request->enabledark ?? 'N';
      $user->emailnotif = $request->emailnotif ?? 'N';
      $user->twofactor = $request->twofactor ?? 'N';

      if ($request->get('new-password') == '' || $request->get('new-password') == null) {
      } else {
        $user->password = $request->get('new-password');
      }
      if ($request->file('avatar')) {
        $user->avatar = $request->file('avatar')->store('/', 'avatars');
      }
      $user->updated_at = Carbon::now();
      $user->save();
    });

    return redirect()->route('users.index')->with('succes', 'User succesfully updated');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(User $user)
  {
    $this->authorize('delete', [User::class, $user]);
    $user->delete();
    return redirect()->route('users.index')->with('succes', 'User succesfully deleted');
  }

  public function getusers(Request $request)
  {
    $this->authorize('viewAny', User::class);
    DB::statement("SET SQL_MODE=''");
    $users = User::select([
      'users.id',
      'users.firstname',
      'users.middlename',
      'users.lastname',
      'users.gender',
      'users.birthday',
      'users.email',
      'users.agency_id',
      'users.staff_id',
      'users.division_id',
      'users.position_id',
      'users.location',
      'users.phone',
      'users.avatar',
      'users.role_id',
      'users.emailnotif',
      'users.enabledark',
      'users.twofactor',
      'users.twofactortype',
      DB::raw("CONCAT(users.firstname, ' ', users.lastname) as fullname"),
    ])
      ->with([
        'staff:id,name,abbreviation',
        'role:id,name',
        'agency:id,UACS_AGY_DSC,Abbreviation',
        'position:id,name',
        'trusted_devices:id,user_id,device_name,ip,last_seen_at,expires_at,revoked_at',
      ])
      ->groupBy('users.id');

    return DataTables::of($users)
      ->editColumn('designation', function (User $user) {
        return optional($user->position)->name;
      })
      ->editColumn('staff', function (User $user) {
        return optional($user->staff)->name;
      })
      ->addColumn('agency', function (User $user) {
        return optional($user->agency)->display_name;
      })
      ->addColumn('role', function (User $user) {
        if ($user->role == null) {
          return null;
        }
        if (auth()->user()->can('edit', [Role::class, $user->role])) {
          return '<div class="btn-group" role="group"><a data-bs-toggle="tooltip" data-bs-original-title="Update Role" class="border-0 bg-transparent mx-0" href="' . route('roles.edit', $user->role_id) . '">' . $user->role->name . '</a></div>';
        } else {
          return $user->role->name;
        }
      })


      ->addColumn('photo', function (User $user) {
        // return '<img src="' . $user->avatarUrl() . '" alt="..." class="border-radius-lg shadow-sm height-50 w-auto">';
        return '<img src="' . $user->avatarUrl() . '" alt="..." class="border-radius-lg shadow-sm height-50" style="width: 50px !important; object-fit: cover !important;">';
        // return '<div class="image-wrapper"><img src="' . $user->avatarUrl() . '" alt="..." class="border-radius-lg shadow-sm"></div>';
      })
      ->addColumn('actions', function (User $user) {
        $delete = '';
        $edit = '';
        if (auth()->user()->can('delete', [User::class, $user])) {
          $delete = '<form action="' . route('users.destroy', $user->id) . '" method="post"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button onclick="return confirm(\'Are you sure you want to delete?\')" data-bs-toggle="tooltip" data-bs-original-title="Delete" class="border-0 bg-transparent"><i class="fa fa-times text-danger"></i></button></form>';
        }
        if (auth()->user()->can('edit', [User::class, $user])) {
          $edit = '<button data-bs-toggle="tooltip" data-bs-original-title="Edit" class="border-0 bg-transparent" onclick=\'showUser(' . json_encode($user, JSON_HEX_APOS) . ', "' . route('users.update', $user->id) . '", "' . $user->avatarUrl() . '")\'><i class="fa fa-pencil text-info"></i></button>';
        } else if (auth()->id() == $user->id) {
          $edit = '<a data-bs-toggle="tooltip" data-bs-original-title="My Profile" class="border-0 bg-transparent px-1" href="' . route('user-profile') . '"><i class="fa fa-user text-success"></i></a>';
        }
        return '<div class="btn-group" role="group">' . $edit . $delete . '</div>';
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
          $query->where('name', 'like', "%{$keyword}%")
            ->orWhere('abbreviation', 'like', "%{$keyword}%");
        });
      })
      ->filterColumn('agency.UACS_AGY_DSC', function ($query, $keyword) {
        $query->whereHas('agency', function ($query) use ($keyword) {
          $query->where('UACS_AGY_DSC', 'like', "%{$keyword}%")
            ->orWhere('Abbreviation', 'like', "%{$keyword}%");
        });
      })
      ->filterColumn('position.name', function ($query, $keyword) {
        $query->whereHas('position', function ($query) use ($keyword) {
          $query->where('name', 'like', "%{$keyword}%");
        });
      })
      ->filterColumn('role.name', function ($query, $keyword) {
        $query->whereHas('role', function ($query) use ($keyword) {
          $query->where('name', 'like', "%{$keyword}%");
        });
      })
      ->rawColumns(['photo', 'role', 'actions'])
      ->toJson();
  }
}
