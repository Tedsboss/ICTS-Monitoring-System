<?php

namespace App\Http\Requests;

use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $staff = $this->targetStaff();

        return $actor !== null
            && $staff !== null
            && ($this->isMethod('put') || $this->isMethod('patch'))
            && $actor->can('update', [Staff::class, $staff]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'min:2', 'max:50',
                Rule::unique('staffs', 'name')->ignore($this->targetStaff()?->getKey()),
            ],
            'abbreviation' => ['required', 'string', 'min:2', 'max:50'],
            'head_name' => ['required', 'string', 'min:2', 'max:50'],
            'head_position' => ['required', 'string', 'min:2', 'max:50'],
            'head_email' => ['required', 'email:rfc,dns', 'not_regex:/\s/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $staff = $this->targetStaff();

        if ($staff) {
            $this->merge([
                'staffTitle' => 'Edit: ' . ($staff->abbreviation ?: $staff->name),
                'staffAction' => route('staffs.update', $staff->getKey()),
                'staffMethod' => 'put',
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return route('staffs.index');
    }

    private function targetStaff(): ?Staff
    {
        $staff = $this->route('staff');

        if ($staff instanceof Staff) {
            return $staff;
        }

        return is_numeric($staff) ? Staff::find((int) $staff) : null;
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This staff or office name is already in use.',
            'head_email.email' => 'Please enter a valid email address with a resolvable mail domain.',
            'head_email.not_regex' => 'The head email address must not contain spaces.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'staff or office name',
            'abbreviation' => 'abbreviation',
            'head_name' => 'head name',
            'head_position' => 'head position',
            'head_email' => 'head email address',
        ];
    }
}
