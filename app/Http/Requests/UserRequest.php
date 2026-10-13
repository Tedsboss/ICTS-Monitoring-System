<?php
namespace App\Http\Requests;
use App\Models\Agency;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        if (!$actor) {
            return false;
        }
        $target = $this->route('user');
        if ($target instanceof User) {
            return $actor->can('edit', [User::class, $target]);
        }
        if (is_numeric($target)) {
            $target = User::find((int) $target);
            return $target !== null && $actor->can('edit', [User::class, $target]);
        }
        if ($target === null && $this->isMethod('post')) {
            return $actor->can('create', User::class);
        }
        return false;
    }
    public function rules(): array
    {
        $userId = $this->currentUserId();
        $requiresStaffDivision = $this->requiresStaffDivision();
        $agencyRules = ['required', 'integer', 'exists:agencies,id'];
        // Match the agency choices offered by UserController::index().
        if (!$this->user()->isSuperAdmin()) {
            $agencyRules[] = Rule::in([$this->user()->agency_id]);
        }
        return [
            'firstname' => ['required', 'string', 'min:1', 'max:255'],
            'middlename' => ['nullable', 'string', 'min:1', 'max:255'],
            'lastname' => ['required', 'string', 'min:1', 'max:255'],
            'email' => [
                'required', 'email:rfc', 'not_regex:/\s/', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'agency_id' => $agencyRules,
            'staff_id' => [Rule::requiredIf($requiresStaffDivision), 'nullable', 'integer', 'exists:staffs,id'],
            'division_id' => [
                Rule::requiredIf($requiresStaffDivision),
                'nullable',
                'integer',
                Rule::exists('divisions', 'id')->where(function (Builder $query) {
                    $query->where('staff_id', $this->input('staff_id'));
                }),
            ],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'role_id' => [
                'required',
                'integer',
                Rule::exists((new Role())->getTable(), 'id')->where(function (Builder $query) {
                    $query->where('name', '!=', 'Super Admin');
                }),
            ],
            'new-password' => [
                $userId === null ? 'required' : 'nullable',
                'string', 'min:6', 'max:255',
            ],
            'confirm-password' => [
                $userId === null ? 'required' : 'required_with:new-password',
                'nullable', 'string', 'min:6', 'max:255', 'same:new-password',
            ],
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            '_editing_user_id' => $this->currentUserId(),
        ]);

        if (! $this->requiresStaffDivision()) {
            $this->merge([
                'staff_id' => null,
                'division_id' => null,
            ]);
        }
    }
    protected function getRedirectUrl(): string
    {
        return route('users.index');
    }
    private function currentUserId(): ?int
    {
        $target = $this->route('user');
        if ($target instanceof User) {
            return (int) $target->getKey();
        }
        return is_numeric($target) ? (int) $target : null;
    }
    private function requiresStaffDivision(): bool
    {
        return Agency::isDepDevId($this->input('agency_id'));
    }
    public function messages(): array
    {
        return [
            'agency_id.in' => 'Please select an agency available to your account.',
            'staff_id.required' => 'The staff/office field is required.',
            'staff_id.exists' => 'Please select a valid staff/office.',
            'division_id.required' => 'The division field is required.',
            'division_id.exists' => 'The selected division does not belong to the selected staff/office.',
            'role_id.exists' => 'Please select a valid DIREK role.',
            'email.unique' => 'This email is already assigned to another user account.',
            'new-password.required' => 'Please set an initial password for the new account.',
            'confirm-password.required' => 'The password confirmation field is required.',
            'confirm-password.required_with' => 'Please confirm the new password.',
            'confirm-password.same' => 'The password confirmation does not match the new password.',
        ];
    }
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
