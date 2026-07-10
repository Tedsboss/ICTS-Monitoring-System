<?php

namespace App\Http\Requests;

use App\Models\Agency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Position;
use App\Models\Staff;
use App\Models\Division;
use App\Models\Unit;
use App\Models\OfficeLocation;
use App\Models\Role;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

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
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $requiresStaffDivision = $this->requiresStaffDivision();

    return [
      'firstname' => ['required', 'string', 'min:1', 'max:255'],
      'middlename' => ['nullable', 'string', 'min:1', 'max:255'],
      'lastname' => ['required', 'string', 'min:1', 'max:255'],
      'gender' => ['nullable', 'in:Male,Female'],
      'birthday' => ['nullable', 'date', 'before_or_equal:now'],
      'agency_id' => ['required', 'integer', 'exists:agencies,id'],
      'position_id' => ['required', 'nullable', 'integer', 'exists:positions,id'],
      'phone' => ['required', 'string', 'min:11', 'max:13'],
      'staff_id' => [Rule::requiredIf($requiresStaffDivision), 'nullable', 'integer', 'exists:staffs,id'],
      'division_id' => [Rule::requiredIf($requiresStaffDivision), 'nullable', 'integer', Rule::exists('divisions', 'id')->where(function (Builder $query) {
        return $query->where('staff_id', $this->input('staff_id'));
      })],
      'location' => ['nullable', 'string', 'min:1', 'max:255'],
      'role_id' => ['required', 'integer', 'exists:' . (new Role)->getTable() . ',id'],

      // 'emailnotif' => ['required', 'in:Y,N', Rule::prohibitedIf($this->input('role_id') == 4 && $this->input('emailnotif') == 'N')],
      'avatar' => ['nullable', 'image'],
      // 'email' => ['required', 'email', Rule::unique('users')->ignore($this->route()->user->id ?? null)],
      'email' => [
        'required',
        'email:rfc',
        'not_regex:/\s/',
        Rule::unique('users', 'email')->ignore($this->route()->user->id ?? null),
      ],
      'new-password' => [$this->route()->user == null ? 'required' : 'nullable', 'min:6'],
      'confirm-password' => [$this->route()->user == null ? 'required' : 'required_with:new-password', $this->route()->user == null ? 'required' : 'nullable', 'min:6', 'same:new-password'],
    ];
  }

  protected function prepareForValidation(): void
  {
    if (!$this->requiresStaffDivision()) {
      $this->merge([
        'staff_id' => null,
        'division_id' => null,
      ]);
    }
  }

  private function requiresStaffDivision(): bool
  {
    return Agency::isDepDevId($this->input('agency_id'));
  }

  public function messages(): array
  {
    return [
      'emailnotif.prohibited' => 'The email notification is required for user role',
      'staff_id.required' => 'The staff field is required',
      'staff_id.required_if' => 'The staff field is required',
      'division_id.required' => 'The division field is required',
      'division_id.required_if' => 'The division field is required',
      'position_id.required_if' => 'The position field is required',
      'email.unique' => 'This email already has an active/pending account.',
    ];
  }

  public function attributes(): array
  {
    return [
      'role_id' => 'role',
      'agency_id' => 'agency',
      'confirm-email' => 'confirmed email',
      'new-password' => 'new password',
      'confirm-password' => 'confirmed password',
    ];
  }
}
