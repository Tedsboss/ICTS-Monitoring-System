<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();

        if (! $actor) {
            return false;
        }

        $role = $this->targetRole();

        if ($role && ($this->isMethod('put') || $this->isMethod('patch'))) {
            return $actor->can('edit', [Role::class, $role]);
        }

        if ($this->route('role') === null && $this->isMethod('post')) {
            return $actor->can('create', Role::class);
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->targetRole()?->getKey()),
            ],
            'description' => [
                'required',
                'string',
                'min:1',
                'max:1000',
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('permissions', 'id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $role = $this->targetRole();

        // Recover modal metadata from the route rather than posted form values.
        $this->merge([
            'roleTitle' => $role ? 'Edit Role: ' . $role->name : 'New Role',
            'roleAction' => $role
                ? route('roles.update', $role->getKey())
                : route('roles.store'),
            'roleMethod' => $role ? 'put' : 'post',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        $role = $this->targetRole();

        return $role
            ? route('roles.edit', $role->getKey())
            : route('roles.index');
    }

    private function targetRole(): ?Role
    {
        $role = $this->route('role');

        if ($role instanceof Role) {
            return $role;
        }

        return is_numeric($role) ? Role::find((int) $role) : null;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a role name.',
            'name.unique' => 'This role name is already in use.',
            'description.required' => 'Please describe the responsibilities of this role.',
            'description.max' => 'The description must not exceed 1,000 characters.',
            'permissions.array' => 'Please submit a valid permission selection.',
            'permissions.*.required' => 'A selected permission is missing its ID.',
            'permissions.*.integer' => 'A selected permission has an invalid ID.',
            'permissions.*.distinct' => 'The same permission was selected more than once.',
            'permissions.*.exists' => 'A selected permission is no longer available.',
        ];
    }
}
