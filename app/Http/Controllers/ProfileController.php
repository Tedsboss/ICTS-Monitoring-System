<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Agency;
use App\Models\Division;
use App\Models\Position;
use App\Models\Staff;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Traits\GenerateLogs;

class ProfileController extends Controller
{
    use GenerateLogs;

    public function show()
    {
        $agencies = Agency::query()
            ->orderBy('UACS_AGY_DSC')
            ->get();

        $staffs = Staff::query()
            ->orderBy('office_id')
            ->orderBy('name')
            ->get();

        $divisions = Division::query()
            ->orderBy('name')
            ->get();

        $positions = Position::query()
            ->orderBy('name')
            ->get();

        $depDevAgencyIds = Agency::depDevIds();

        return view('users.user-profile', compact(
            'agencies',
            'staffs',
            'divisions',
            'positions',
            'depDevAgencyIds'
        ));
    }

    public function update(ProfileRequest $request)
    {
        $profile = auth()->user();
        $oldFirstLogin = $profile->first_login;

        // Personal profile information.
        $profile->gender = $request->gender;
        $profile->birthday = $request->birthday;
        $profile->position_id = $request->position_id;
        $profile->location = $request->location;
        $profile->phone = $request->phone;

        // Agency may only be changed by Super Admin or during initial setup.
        if ($profile->isSuperAdmin()) {
            $profile->agency_id = $request->agency_id;
        } elseif (
            $profile->first_login === 'Y' &&
            empty($profile->agency_id)
        ) {
            $profile->agency_id = $request->agency_id;
        }

        // Staff and Division are required only for DepDev users.
        if (
            $profile->first_login === 'Y' &&
            Agency::isDepDevId($profile->agency_id)
        ) {
            $profile->staff_id = $request->staff_id;
            $profile->division_id = $request->division_id;
        } elseif (!Agency::isDepDevId($profile->agency_id)) {
            $profile->staff_id = null;
            $profile->division_id = null;
        }

        // User preferences.
        $profile->enabledark = $request->enabledark ?? 'N';

        if (
            $profile->can(
                'enableMyEmailNotification',
                [User::class, $profile]
            )
        ) {
            $profile->emailnotif = $request->emailnotif ?? 'N';
        }

        // Two-factor authentication.
        $profile->twofactor = $request->twofactor ?? 'N';

        if ($profile->twofactor === 'Y') {
            $profile->twofactortype = $request->twofactortype ?: 'Email';
        } else {
            $profile->twofactortype = null;
        }

        // Password is changed only when a new password was provided.
        if ($request->filled('new-password')) {
            $profile->password = $request->get('new-password');
        }

        // Store the new avatar when one was uploaded.
        if ($request->hasFile('avatar')) {
            $profile->avatar = $request
                ->file('avatar')
                ->store('/', 'avatars');
        }

        // Initial profile setup is complete after a successful update.
        $profile->first_login = 'N';

        $profile->save();

        // Keep the current session theme synchronized.
        session([
            'user_settings' => [
                'class_theme' => $profile->enabledark === 'Y'
                    ? 'dark'
                    : '',
            ],
        ]);

        if ($oldFirstLogin === 'Y') {
            return redirect()
                ->route('home')
                ->with(
                    'succes',
                    'Profile updated successfully. You may now proceed to use the system'
                );
        }

        return back()->with(
            'succes',
            'Profile successfully updated'
        );
    }

    public function revoke(TrustedDevice $trusteddevice)
    {
        $this->authorize(
            'view',
            [TrustedDevice::class, $trusteddevice]
        );

        $remarks = '';

        if ($trusteddevice->user_id == auth()->id()) {
            $remarks = 'own ';
        }

        $trusteddevice->revoked_at = now();
        $trusteddevice->save();

        $this->addSystemLogs(
            'Revoked ' .
                $remarks .
                'trusted device: ' .
                $trusteddevice->device_name .
                ' - ' .
                $trusteddevice->ip,
            auth()->id(),
            auth()->user()->email,
            request()->getClientIp(true),
            'trusted_devices',
            $trusteddevice->id
        );

        return back()->with(
            'succes',
            'Device successfully revoked'
        );
    }
}
