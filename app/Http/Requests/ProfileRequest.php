<?php

namespace App\Http\Requests;

use App\Models\Agency;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $user = auth()->user();
        $requiresAgency = $user->isSuperAdmin()
            || ($user->first_login === 'Y' && empty($user->agency_id));
        $requiresStaffDivision = $user->first_login === 'Y' && $this->requiresStaffDivision();
        $requiresPassword = $user->first_login === 'Y';

        return [
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            // HTML date inputs submit YYYY-MM-DD; reject blank or malformed dates.
            'birthday' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'agency_id' => [Rule::requiredIf($requiresAgency), 'nullable', 'integer', 'exists:agencies,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'phone' => ['required', 'string', 'min:11', 'max:20'],
            'staff_id' => [Rule::requiredIf($requiresStaffDivision), 'nullable', 'integer', 'exists:staffs,id'],
            'division_id' => [
                Rule::requiredIf($requiresStaffDivision),
                'nullable',
                'integer',
                Rule::exists('divisions', 'id')->where(function (Builder $query) {
                    $query->where('staff_id', $this->input('staff_id'));
                }),
            ],
            'location' => ['required', 'string', 'max:255'],
            'emailnotif' => ['nullable', Rule::in(['Y'])],
            'enabledark' => ['nullable', Rule::in(['Y'])],
            'twofactor' => ['nullable', Rule::in(['Y'])],
            // The method is stored even when 2FA is off. Only Email is available.
            'twofactortype' => ['required', Rule::in(['Email'])],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'old-password' => [
                Rule::requiredIf($requiresPassword || $this->filled('new-password')),
                'nullable', 'string',
            ],
            'new-password' => [
                Rule::requiredIf($requiresPassword),
                'required_with:old-password',
                'nullable', 'string', 'min:6', 'different:old-password',
            ],
            'confirm-password' => [
                Rule::requiredIf($requiresPassword),
                'required_with:new-password',
                'nullable', 'string', 'min:6', 'same:new-password',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (!$this->filled('old-password')) {
                    return;
                }

                $user = auth()->user();
                if (!$user || !Hash::check($this->input('old-password'), $user->password)) {
                    $validator->errors()->add('old-password', 'The current password is incorrect.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->requiresStaffDivision()) {
            $this->merge(['staff_id' => null, 'division_id' => null]);
        }

        // A missing/blank method defaults to Email, independently of the 2FA flag.
        // Supplied unsupported methods still fail validation.
        if (!$this->filled('twofactortype')) {
            $this->merge(['twofactortype' => 'Email']);
        }
    }

    private function requiresStaffDivision(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $agencyId = $this->input('agency_id', $user->agency_id);
        return Agency::isDepDevId($agencyId);
    }

    public function messages(): array
    {
        return [
            'birthday.required' => 'Please select your birth date.',
            'birthday.date_format' => 'Please select a valid birth date in YYYY-MM-DD format.',
            'birthday.before_or_equal' => 'Your birth date cannot be in the future.',
            'old-password.required' => 'Please enter your current password.',
            'new-password.required' => 'Please enter a new password.',
            'new-password.required_with' => 'Please enter a new password.',
            'new-password.different' => 'The new password must be different from your current password.',
            'confirm-password.required' => 'Please confirm your new password.',
            'confirm-password.required_with' => 'Please confirm your new password.',
            'confirm-password.same' => 'The password confirmation does not match the new password.',
            'staff_id.required' => 'Please select your staff or office.',
            'division_id.required' => 'Please select your division.',
            'division_id.exists' => 'The selected division does not belong to the selected staff or office.',
            'twofactortype.required' => 'Please select a two-factor authentication method.',
            'twofactortype.in' => 'The selected two-factor authentication method is not available.',
            'avatar.image' => 'The profile photo must be a valid image.',
            'avatar.mimes' => 'The profile photo must be a JPG, JPEG, PNG, or WEBP image.',
            'avatar.max' => 'The profile photo must not be larger than 5 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'birthday' => 'birth date',
            'agency_id' => 'agency',
            'position_id' => 'position',
            'staff_id' => 'staff / office',
            'division_id' => 'division',
            'twofactortype' => 'two-factor authentication method',
            'old-password' => 'current password',
            'new-password' => 'new password',
            'confirm-password' => 'password confirmation',
            'enabledark' => 'dark theme',
            'emailnotif' => 'email notifications',
        ];
    }
}
