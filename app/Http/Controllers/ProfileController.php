<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Agency;
use App\Models\Staff;
use App\Models\Division;
use App\Models\Unit;
use App\Models\Position;
use App\Models\OfficeLocation;
use App\Models\TrustedDevice;
use App\Traits\GenerateLogs;

class ProfileController extends Controller
{

  use GenerateLogs;

  public function show()
  {
    $agencies = Agency::query()->orderBy('UACS_AGY_DSC')->get();
    $staffs = Staff::orderBy('office_id', 'asc')->orderBy('name', 'asc')->get();
    $divisions = Division::all();
    $positions = Position::all();
    $depDevAgencyIds = Agency::depDevIds();
    return view('users.user-profile', compact('agencies', 'staffs', 'divisions', 'positions', 'depDevAgencyIds'));
  }

  public function update(ProfileRequest $request)
  {
    $profile = auth()->user();
    $old_first_login = $profile->first_login;
    $profile->gender = $request->gender;
    $profile->birthday = $request->birthday;

    if ($profile->isSuperAdmin()) {
      $profile->agency_id = $request->agency_id;
    } elseif ($profile->first_login == 'Y' && empty($profile->agency_id)) {
      $profile->agency_id = $request->agency_id;
    }

    $profile->position_id = $request->position_id;
    if ($profile->first_login == 'Y' && Agency::isDepDevId($profile->agency_id)) {
      $profile->staff_id = $request->staff_id;
      $profile->division_id = $request->division_id;
    } elseif (!Agency::isDepDevId($profile->agency_id)) {
      $profile->staff_id = null;
      $profile->division_id = null;
    }

    $profile->location = $request->location;
    $profile->phone = $request->phone;

    $profile->enabledark = $request->enabledark ?? 'N';
    if ($profile->can('enableMyEmailNotification', [User::class, $profile])) {
      $profile->emailnotif = $request->emailnotif ?? 'N';
    }
    $profile->twofactor = $request->twofactor ?? 'N';

    if ($request->get('new-password') == '' || $request->get('new-password') == null) {
    } else {
      $profile->password = $request->get('new-password');
    }

    if ($request->file('avatar')) {
      auth()->user()->update(['avatar' => $request->file('avatar')->store('/', 'avatars')]);
    }

    $profile->first_login = 'N';
    $profile->save();

    session(['user_settings' => [
      'class_theme' => $request->enabledark == 'Y' ? 'dark' : '',
    ]]);

    if ($old_first_login == 'Y') {
      return redirect()->route('home')->with('succes', 'Profile updated successfully. You may now proceed to use the system');
    }
    return back()->with('succes', 'Profile succesfully updated');
  }

  public function revoke(TrustedDevice $trusteddevice)
  {
    $this->authorize('view', [TrustedDevice::class, $trusteddevice]);
    $remarks = '';
    if ($trusteddevice->user_id == auth()->id()) {
      $remarks = 'own ';
    }
    $trusteddevice->revoked_at = now();
    $trusteddevice->save();
    $this->addSystemLogs("Revoked " . $remarks . "trusted device: " . $trusteddevice->device_name . ' - ' . $trusteddevice->ip, auth()->id(), auth()->user()->email, request()->getClientIp(true), 'trusted_devices', $trusteddevice->id);
    return back()->with('succes', 'Device succesfully revoked');
  }
}
