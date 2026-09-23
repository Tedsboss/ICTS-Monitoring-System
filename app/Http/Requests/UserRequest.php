<?php

namespace App\Http\Requests;

use App\Models\Agency;
use App\Models\Role;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->currentUserId();
        $requiresStaffDivision = $this->requiresStaffDivision();

        return [
            'firstname' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],

            'middlename' => [
                'nullable',
                'string',
                'min:1',
                'max:255',
            ],

            'lastname' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],

            'email' => [
                'required',
                'email:rfc',
                'not_regex:/\s/',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'agency_id' => [
                'required',
                'integer',
                'exists:agencies,id',
            ],

            'staff_id' => [
                Rule::requiredIf($requiresStaffDivision),
                'nullable',
                'integer',
                'exists:staffs,id',
            ],

            'division_id' => [
                Rule::requiredIf($requiresStaffDivision),
                'nullable',
                'integer',
                Rule::exists('divisions', 'id')
                    ->where(function (Builder $query) {
                        $query->where(
                            'staff_id',
                            $this->input('staff_id')
                        );
                    }),
            ],

            'position_id' => [
                'nullable',
                'integer',
                'exists:positions,id',
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists((new Role())->getTable(), 'id')
                    ->where(function (Builder $query) {
                        $query->where('name', '!=', 'Super Admin');
                    }),
            ],

            'new-password' => [
                $userId === null ? 'required' : 'nullable',
                'string',
                'min:6',
                'max:255',
            ],

            'confirm-password' => [
                $userId === null
                    ? 'required'
                    : 'required_with:new-password',
                'nullable',
                'string',
                'min:6',
                'max:255',
                'same:new-password',
            ],
        ];
    }

    /**
     * Prepare request data before validation.
     */
    protected function prepareForValidation(): void
    {
        $userId = $this->currentUserId();

        // Remember whether validation came from Edit User.
        $this->merge([
            '_editing_user_id' => $userId,
        ]);

        // Non-DepDev accounts do not use Staff/Office and Division.
        if (! $this->requiresStaffDivision()) {
            $this->merge([
                'staff_id' => null,
                'division_id' => null,
            ]);
        }
    }

    /**
     * Always return validation failures to the modal-based
     * User Management page.
     */
    protected function getRedirectUrl(): string
    {
        return route('users.index');
    }

    /**
     * Get the user ID from the current resource route.
     */
    private function currentUserId(): ?int
    {
        $user = $this->route('user');

        if ($user === null) {
            return null;
        }

        if (is_object($user) && isset($user->id)) {
            return (int) $user->id;
        }

        if (is_numeric($user)) {
            return (int) $user;
        }

        return null;
    }

    /**
     * Determine whether Staff/Office and Division are required.
     */
    private function requiresStaffDivision(): bool
    {
        return Agency::isDepDevId(
            $this->input('agency_id')
        );
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'staff_id.required' =>
                'The staff/office field is required.',

            'staff_id.exists' =>
                'Please select a valid staff/office.',

            'division_id.required' =>
                'The division field is required.',

            'division_id.exists' =>
                'The selected division does not belong to the selected staff/office.',

            'role_id.exists' =>
                'Please select a valid DIREK role.',

            'email.unique' =>
                'This email is already assigned to another user account.',

            'confirm-password.required' =>
                'The password confirmation field is required.',

            'confirm-password.required_with' =>
                'Please confirm the new password.',

            'confirm-password.same' =>
                'The password confirmation does not match the new password.',
        ];
    }

    /**
     * Friendly field names used by validation messages.
     */
    public function attributes(): array
    {
        return [
            'firstname' => 'first name',
            'middlename' => 'middle name',
            'lastname' => 'last name',
            'agency_id' => 'agency',
            'staff_id' => 'staff/office',
            'division_id' => 'division',
            'position_id' => 'position',
            'role_id' => 'role',
            'new-password' => 'new password',
            'confirm-password' => 'password confirmation',
        ];
    }
}
