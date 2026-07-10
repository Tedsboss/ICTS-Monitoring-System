<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgencyRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'UACS_AGY_DSC' => ['nullable', 'string', 'max:100'],
      'active' => ['nullable', 'in:0,1'],
      'head_lname' => ['nullable', 'string', 'max:50'],
      'head_mname' => ['nullable', 'string', 'max:50'],
      'head_fname' => ['nullable', 'string', 'max:50'],
      'head_designation' => ['nullable', 'string', 'max:100'],
      'head_telnumber' => ['nullable', 'string', 'max:50'],
      'head_email' => ['nullable', 'email', 'max:50'],
    ];
  }

  public function attributes(): array
  {
    return [
      'UACS_AGY_DSC' => 'agency name',
      'head_lname' => 'head last name',
      'head_mname' => 'head middle name',
      'head_fname' => 'head first name',
      'head_designation' => 'head designation',
      'head_telnumber' => 'head telephone number',
      'head_email' => 'head email',
    ];
  }
}
