<?php

namespace App\Http\Requests;

use App\Rules\EndDateTime;
use App\Rules\NoSuspensionOverlap;
use App\Rules\StartDateTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HolidayRequest extends FormRequest
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
    return [
      'name' => ['required', 'string', 'max:255', 'min:2', Rule::unique('holidays')->ignore($this->route()->holiday->id ?? null)],
      'type' => ['required', 'in:Holiday,Suspension'],
      'whole_day' => ['required', 'in:Y,N', Rule::prohibitedIf($this->input('type') == 'Holiday' && $this->input('whole_day') == 'N')],
      'repeat_every_year' => ['required', 'in:Y,N', Rule::prohibitedIf($this->input('type') == 'Suspension' && $this->input('repeat_every_year') == 'Y')],
      'start' => ['required', 'date', new StartDateTime($this->input('type'), $this->input('whole_day'))],
      'end' => ['required', 'date', new EndDateTime($this->input('type'), $this->input('whole_day'), $this->input('start')), new NoSuspensionOverlap($this->input('start'), $this->route()->holiday->id ?? null)],
    ];
  }
}
