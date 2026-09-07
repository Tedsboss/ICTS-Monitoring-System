<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by RoleController / RolePolicy.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleId = optional(
            $this->route('role')
        )->id;

        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($roleId),
            ],

            'description' => [
                'required',
                'string',
                'min:1',
                'max:1000',
            ],

            // Permission selection is optional.
            'permissions' => [
                'nullable',
                'array',
            ],

            // Validate every submitted permission individually.
            'permissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id'),
            ],
        ];
    }
}
