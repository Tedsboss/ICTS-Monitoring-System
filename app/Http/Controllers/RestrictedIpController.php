<?php

namespace App\Http\Controllers;

use App\Models\Parameter;
use App\Models\RestrictedIp;
use App\Traits\GenerateLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class RestrictedIpController extends Controller
{
  use GenerateLogs;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $this->authorize('viewAny', RestrictedIp::class);
    return view('restrictedips.index');
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
    $this->authorize('create', RestrictedIp::class);
    $request->validate([
      'ipaddress' => [
        'required',
        'ip',
        function ($attribute, $value, $fail) {
          if ($value === request()->ip()) {
            $fail('The :attribute should not be your own IP address.');
          }
        },
      ],
      'content' => ['nullable', 'string'],
    ]);

    $restricted_ip = new RestrictedIp();
    $restricted_ip->ipaddress = $request->ipaddress;
    $restricted_ip->route = 'restrictedips';
    $restricted_ip->content = $request->content;
    $restricted_ip->status = 1;
    $restricted_ip->updated_by = auth()->id();
    $restricted_ip->save();

    $this->addSystemLogs("Added blocked IP: " . $restricted_ip->ipaddress, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'restricted_ips', $restricted_ip->id);

    return redirect()->route('restrictedips.index')->withStatus('New IP has been restricted');
  }

  /**
   * Display the specified resource.
   */
  public function show(RestrictedIp $restrictedIp)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(RestrictedIp $restrictedIp)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, RestrictedIp $restrictedIp)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(RestrictedIp $restrictedip)
  {
    $this->authorize('delete', [RestrictedIp::class, $restrictedip]);
    $restrictedip->delete();
    Artisan::call('cache:clear');
    return back()->with('succes', 'IP has been deleted');
  }

  public function updatestatus(RestrictedIp $restrictedip)
  {
    $this->authorize('create', RestrictedIp::class);
    $restrictedip->status = $restrictedip->status == 1 ? 2 : 1;
    $restrictedip->save();

    return redirect()->route('restrictedips.index')->with('succes', 'IP has been updated');
  }

  public function blocked()
  {
    $icc_telephone = Parameter::where('id', 27)->first()->value;
    $icc_email = Parameter::where('id', 28)->first()->value;
    return view('errors.blocked', compact('icc_telephone', 'icc_email'));
  }

  public function getrestrictedips(Request $request)
  {
    $this->authorize('viewAny', RestrictedIp::class);
    $cancreate = false;
    if (auth()->user()->can('create', RestrictedIp::class)) {
      $cancreate = true;
    }
    DB::statement("SET SQL_MODE=''");
    $restrictedips = RestrictedIp::select([
      'restricted_ips.id',
      'restricted_ips.ipaddress',
      'restricted_ips.content',
      'restricted_ips.status',
      'restricted_ips.updated_by',
      'restricted_ips.updated_at',
    ])
      ->with([
        'editor:id,firstname,lastname,middlename',
      ])
      ->groupBy('restricted_ips.id');

    return DataTables::of($restrictedips)
      ->editColumn('updated_at', function (RestrictedIp $restrictedip) {
        return $restrictedip->updated_at->format('Y-m-d H:i:s');
      })
      ->addColumn('editor', function (RestrictedIp $restrictedip) {
        if ($restrictedip->editor == null) {
          return null;
        }
        return $restrictedip->editor->full_name;
      })
      ->filterColumn('editor', function ($query, $keyword) {
        $query->whereHas('editor', function ($q) use ($keyword) {
          $q->whereRaw("CONCAT(firstname, ' ', lastname) like ?", "%{$keyword}%");
        });
      })
      ->editColumn('status', function (RestrictedIp $restrictedip) {
        return $restrictedip->status == 1 ? 'Blocked' : 'Unblocked';
      })
      ->addColumn('actions', function (RestrictedIp $restrictedip) use ($cancreate) {
        $delete = '';
        if (auth()->user()->can('delete', [RestrictedIp::class, $restrictedip])) {
          $delete = '<form action="' . route('restrictedips.destroy', $restrictedip->id) . '" method="post"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button onclick="return confirm(\'Are you sure you want to remove the IP?\')" data-bs-toggle="tooltip" data-bs-original-title="Delete" class="border-0 bg-transparent"><i class="fa fa-times text-danger"></i></button></form>';
        }
        $enable = '';
        if ($cancreate) {
          $enable = '<a href="' . route('restrictedips.updatestatus', $restrictedip->id) . '" class="px-1" data-bs-toggle="tooltip" data-bs-original-title="' . ($restrictedip->status == 1 ? 'Blocked' : 'Unblocked') . '"><i class="fa ' . ($restrictedip->status == 1 ? 'fa-lock text-secondary' : 'fa-unlock text-success') . '"></i></a>';
        }
        return '<div class="btn-group" role="group"><a data-bs-toggle="tooltip" data-bs-original-title="Show More" class="mx-2" onclick=\'showIP(' . json_encode($restrictedip, JSON_HEX_APOS) . ')\'><i class="fa fa-eye text-info"></i></a>' . $enable . $delete . '</div>';
      })
      ->rawColumns(['actions'])
      ->toJson();
  }
}
