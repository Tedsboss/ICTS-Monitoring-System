<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Position;
use App\Models\Staff;
use App\Models\Division;
use App\Models\Unit;
use App\Models\OfficeLocation;
use App\Models\Agency;
use Illuminate\Database\Query\Builder;

class ProfileRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return auth()->check();
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $user = auth()->user();
    $requiresAgency = $user->isSuperAdmin() || ($user->first_login == 'Y' && empty($user->agency_id));
    $requiresStaffDivision = $user->first_login == 'Y' && $this->requiresStaffDivision();

    return [
      'gender' => ['required', 'in:Male,Female'],
      'birthday' => ['required', 'date', 'before_or_equal:now'],
      'agency_id' => [Rule::when($requiresAgency, 'required'), 'nullable', 'integer', 'exists:agencies,id'],
      'position_id' => ['required', 'nullable', 'integer', 'exists:positions,id'],
      'phone' => ['required', 'string', 'min:11', 'max:13'],
      'staff_id' => [Rule::requiredIf($requiresStaffDivision), 'nullable', 'integer', 'exists:staffs,id'],
      'division_id' => [Rule::requiredIf($requiresStaffDivision), 'nullable', 'integer', Rule::exists('divisions', 'id')->where(function (Builder $query) {
        return $query->where('staff_id', $this->input('staff_id'));
      })],
      'location' => ['required', 'string', 'min:1', 'max:255'],
      'emailnotif' => ['nullable', 'in:Y'],
      'enabledark' => ['nullable', 'in:Y'],
      'twofactor' => ['nullable', 'in:Y'],
      'twofactortype' => ['nullable', 'in:Email'],
      // 'twofactortype' => ['nullable', 'in:Email,SMS,Authenticator App'],
      'avatar' => ['nullable', 'image'],
      'old-password' => [Rule::when($user->first_login == 'Y', 'required', 'required_with:new-password'), 'nullable'],
      'new-password' => [Rule::when($user->first_login == 'Y', 'required', 'required_with:old-password'), 'nullable', 'min:6', 'different:old-password'],
      'confirm-password' => [Rule::when($user->first_login == 'Y', 'required', 'required_with:new-password'), 'nullable', 'min:6', 'same:new-password'],
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
    $user = auth()->user();
    $agencyId = $this->input('agency_id', $user->agency_id);

    return Agency::isDepDevId($agencyId);
  }

  public function attributes(): array
  {
    return [
      'agency_id' => 'agency',
      'confirm-email' => 'confirmed email',
      'old-password' => 'old password',
      'new-password' => 'new password',
      'confirm-password' => 'confirmed password',
    ];
  }
}
