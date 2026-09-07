@php
    $class_theme = session('user_settings.class_theme', '');
    $selectedPermissions = collect(
        old('permissions', $role_permissions ?? [])
    )
        ->map(function ($permissionId) {
            return (int) $permissionId;
        })
        ->values()
        ->toArray();
    // DIREK Administration modules
    $administrationModuleNames = [
        'User Management',
        'Roles and Permissions',
        'Staff Management',
        'Division Management',
        'System Parameters',
        'System Logs',
    ];
    // DIREK Financial Management modules
    $financialManagementModuleNames = [
        'Financial Plan',
        'Allocation Type Management',
        'Procurement',
        'SAEB',
    ];
    // Match Administration modules
    $administrationModules = $modules->filter(function ($module) use ($administrationModuleNames) {
        return collect($administrationModuleNames)->contains(function ($name) use ($module) {
            return strcasecmp(
                trim((string) $module->name),
                trim((string) $name)
            ) === 0;
        });
    });
    // Match Financial Management modules
    $financialManagementModules = $modules->filter(function ($module) use ($financialManagementModuleNames) {
        return collect($financialManagementModuleNames)->contains(function ($name) use ($module) {
            return strcasecmp(
                trim((string) $module->name),
                trim((string) $name)
            ) === 0;
        });
    });
    // DIREK permission categories
    $direkCategories = collect([
        [
            'name' => 'Administration',
            'key' => 'administration',
            'description' => 'Manage DIREK users, roles, organizational structure, parameters, and system logs.',
            'icon' => 'fa fa-cog',
            'modules' => $administrationModules,
        ],
        [
            'name' => 'Financial Management',
            'key' => 'financial-management',
            'description' => 'Manage access to Financial Plan, Allocation Type Management, Procurement, and SAEB functions.',
            'icon' => 'fa fa-money',
            'modules' => $financialManagementModules,
        ],
    ])->filter(function ($category) {
        return $category['modules']->isNotEmpty();
    });
@endphp
@extends('layouts.app')
@section('content')
    {{-- Navbar --}}
    <nav
        class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
        id="navbarBlur"
        data-scroll="false"
    >
        <div class="container-fluid py-1 px-3">
            @include('layouts.navbars.auth.topnav', [
                'title' => 'Edit: ' . $role->name,
                'subtitle' => 'Edit',
                'links' => [
                    [
                        'name' => 'Roles & Permissions',
                        'url' => route('roles.index'),
                    ],
                ],
            ])
            @include('layouts.navbars.auth.topnav-withdatetime')
        </div>
    </nav>
    {{-- Role form --}}
    <form
        method="POST"
        action="{{ route('roles.update', $role->id) }}"
        enctype="multipart/form-data"
        id="frmUpdate"
    >
        @csrf
        @method('put')
        <div class="container-fluid">
            {{-- Validation errors --}}
            @if ($errors->any())
                <div
                    class="mt-4 flex items-start gap-3 rounded-xl
                           border border-rose-200 bg-rose-50
                           px-4 py-3 text-sm text-rose-800"
                >
                    <i
                        class="fa fa-exclamation-circle mt-0.5"
                        aria-hidden="true"
                    ></i>
                    <div class="flex-1">
                        <div class="font-semibold">
                            Please check the information below.
                        </div>
                        <ul class="mb-0 mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            {{-- Role information --}}
            <div class="mt-4">
                <div
                    class="overflow-hidden rounded-2xl
                           border border-slate-200 bg-white shadow-sm"
                >
                    {{-- Header --}}
                    <div
                        class="flex flex-col gap-3
                               border-b border-slate-200
                               px-5 py-5
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0
                                       items-center justify-center
                                       rounded-xl bg-slate-100
                                       text-slate-600"
                            >
                                <i
                                    class="fa fa-key"
                                    aria-hidden="true"
                                ></i>
                            </div>
                            <div>
                                <h2
                                    class="mb-0 text-base
                                           font-bold text-slate-900"
                                >
                                    Role Information
                                </h2>
                                <p
                                    class="mb-0 mt-1
                                           text-sm text-slate-500"
                                >
                                    Update the role details and its DIREK access permissions.
                                </p>
                            </div>
                        </div>
                        <span
                            class="inline-flex w-fit items-center
                                   gap-2 rounded-full
                                   border border-slate-200
                                   bg-slate-50 px-3 py-1.5
                                   text-xs font-semibold
                                   text-slate-600"
                        >
                            <i
                                class="fa fa-user-secret"
                                aria-hidden="true"
                            ></i>
                            {{ $role->name }}
                        </span>
                    </div>
                    {{-- Fields --}}
                    <div class="p-4 sm:p-5">
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            {{-- Name --}}
                            <div class="lg:col-span-2">
                                <label
                                    for="name"
                                    class="mb-2 block
                                           text-sm font-semibold
                                           text-slate-700"
                                >
                                    Role Name
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    name="name"
                                    id="name"
                                    value="{{ old('name', $role->name) }}"
                                    class="form-control"
                                    type="text"
                                    placeholder="Role Name"
                                >
                                @error('name')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            {{-- Description --}}
                            <div class="lg:col-span-2">
                                <label
                                    for="description"
                                    class="mb-2 block
                                           text-sm font-semibold
                                           text-slate-700"
                                >
                                    Description
                                    <span class="text-rose-500">*</span>
                                </label>
                                <textarea
                                    name="description"
                                    id="description"
                                    rows="3"
                                    class="w-100 form-control"
                                    placeholder="Description"
                                >{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <p class="mb-0 mt-1 text-xs text-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            {{-- Updated by --}}
                            <div>
                                <label
                                    class="mb-2 block
                                           text-sm font-semibold
                                           text-slate-700"
                                >
                                    Updated By
                                </label>
                                <input
                                    value="{{ optional($role->creator)->full_name ?? '—' }}"
                                    class="form-control bg-slate-50"
                                    type="text"
                                    readonly
                                >
                            </div>
                            {{-- Updated at --}}
                            <div>
                                <label
                                    class="mb-2 block
                                           text-sm font-semibold
                                           text-slate-700"
                                >
                                    Updated At
                                </label>
                                <input
                                    value="{{ $role->updated_at }}"
                                    class="form-control bg-slate-50"
                                    type="text"
                                    readonly
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Permissions --}}
            <div class="mt-4">
                <div
                    class="overflow-hidden rounded-2xl
                           border border-slate-200
                           bg-white shadow-sm"
                >
                    {{-- Header --}}
                    <div
                        class="flex flex-col gap-3
                               border-b border-slate-200
                               px-5 py-5
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0
                                       items-center justify-center
                                       rounded-xl bg-slate-100
                                       text-slate-600"
                            >
                                <i
                                    class="fa fa-lock"
                                    aria-hidden="true"
                                ></i>
                            </div>
                            <div>
                                <h2
                                    class="mb-0 text-base
                                           font-bold text-slate-900"
                                >
                                    Permissions
                                </h2>
                                <p
                                    class="mb-0 mt-1
                                           text-sm text-slate-500"
                                >
                                    Allow only the DIREK functions this role should access.
                                </p>
                            </div>
                        </div>
                        <div
                            class="inline-flex w-fit
                                   items-center gap-2
                                   rounded-full
                                   border border-slate-200
                                   bg-slate-50
                                   px-3 py-1.5
                                   text-xs font-semibold
                                   text-slate-600"
                        >
                            <i
                                class="fa fa-shield"
                                aria-hidden="true"
                            ></i>
                            DIREK Access
                        </div>
                    </div>
                    {{-- Information --}}
                    <div
                        class="flex items-start gap-2
                               border-b border-sky-100
                               bg-sky-50 px-5 py-3
                               text-xs text-sky-700"
                    >
                        <i
                            class="fa fa-info-circle mt-0.5"
                            aria-hidden="true"
                        ></i>
                        <span>
                            Only modules currently used by DIREK are shown.
                            Legacy permissions remain in the database and are not displayed here.
                        </span>
                    </div>
                    {{-- Permission categories --}}
                    <div
                        id="role-permission-accordion"
                        class="divide-y divide-slate-200"
                    >
                        @forelse ($direkCategories as $direkCategory)
                            @php
                                $categoryName = $direkCategory['name'];
                                $categoryKey = $direkCategory['key'];
                                $categoryDescription = $direkCategory['description'];
                                $categoryIcon = $direkCategory['icon'];
                                $categoryModules = $direkCategory['modules'];
                                $hasCheckedPermission = false;
                                $categoryPermissionCount = 0;
                                $categoryCheckedCount = 0;
                                foreach ($categoryModules as $categoryModule) {
                                    foreach ($categoryModule->permissions as $categoryPermission) {
                                        $categoryPermissionCount++;
                                        if (
                                            in_array(
                                                (int) $categoryPermission->id,
                                                $selectedPermissions,
                                                true
                                            )
                                        ) {
                                            $categoryCheckedCount++;
                                            $hasCheckedPermission = true;
                                        }
                                    }
                                }
                            @endphp
                            <div
                                class="role-permission-category"
                                data-permission-category="{{ $categoryKey }}"
                            >
                                {{-- Category --}}
                                <button
                                    type="button"
                                    class="role-permission-toggle
                                           flex w-full items-center
                                           gap-3 border-0
                                           bg-white px-5 py-4
                                           text-left transition
                                           hover:bg-slate-50"
                                    data-target="rolePermissionPanel{{ $categoryKey }}"
                                    aria-expanded="{{ $hasCheckedPermission ? 'true' : 'false' }}"
                                >
                                    <div
                                        class="flex h-9 w-9 shrink-0
                                               items-center justify-center
                                               rounded-lg bg-slate-100
                                               text-slate-600"
                                    >
                                        <i
                                            class="{{ $categoryIcon }}"
                                            aria-hidden="true"
                                        ></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex flex-wrap
                                                   items-center gap-2"
                                        >
                                            <span
                                                class="text-sm font-bold
                                                       text-slate-900"
                                            >
                                                {{ $categoryName }}
                                            </span>
                                            <span
                                                class="inline-flex
                                                       rounded-full
                                                       bg-slate-100
                                                       px-2.5 py-1
                                                       text-[11px]
                                                       font-semibold
                                                       text-slate-600"
                                            >
                                                {{ $categoryCheckedCount }}
                                                /
                                                {{ $categoryPermissionCount }}
                                                allowed
                                            </span>
                                        </div>
                                        <p
                                            class="mb-0 mt-1
                                                   text-xs text-slate-500"
                                        >
                                            {{ $categoryDescription }}
                                        </p>
                                    </div>
                                    <i
                                        class="fa
                                               {{ $hasCheckedPermission ? 'fa-minus' : 'fa-plus' }}
                                               role-permission-toggle-icon
                                               text-xs text-slate-400"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                                {{-- Category panel --}}
                                <div
                                    id="rolePermissionPanel{{ $categoryKey }}"
                                    class="role-permission-panel bg-slate-50/50"
                                    style="{{ $hasCheckedPermission ? 'display: block;' : 'display: none;' }}"
                                >
                                    <div class="space-y-4 px-5 pb-5">
                                        @foreach ($categoryModules as $module)
                                            @php
                                                $modulePermissions = $module
                                                    ->permissions
                                                    ->sortBy([
                                                        ['order', 'asc'],
                                                        ['id', 'asc'],
                                                    ]);
                                            @endphp
                                            <div
                                                class="overflow-hidden
                                                       rounded-xl
                                                       border border-slate-200
                                                       bg-white"
                                            >
                                                {{-- Module header --}}
                                                <div
                                                    class="flex flex-col gap-2
                                                           border-b
                                                           border-slate-200
                                                           bg-slate-50
                                                           px-4 py-3
                                                           sm:flex-row
                                                           sm:items-center
                                                           sm:justify-between"
                                                >
                                                    <div>
                                                        <h3
                                                            class="mb-0
                                                                   text-sm
                                                                   font-bold
                                                                   text-slate-900"
                                                        >
                                                            {{ $module->name }}
                                                        </h3>
                                                        <p
                                                            class="mb-0 mt-1
                                                                   text-xs
                                                                   text-slate-500"
                                                        >
                                                            Select the actions this role can perform.
                                                        </p>
                                                    </div>
                                                    <span
                                                        class="inline-flex
                                                               w-fit rounded-full
                                                               border
                                                               border-slate-200
                                                               bg-white
                                                               px-2.5 py-1
                                                               text-[11px]
                                                               font-semibold
                                                               text-slate-500"
                                                    >
                                                        {{ $modulePermissions->count() }}
                                                        permissions
                                                    </span>
                                                </div>
                                                {{-- Permission table --}}
                                                <div class="overflow-x-auto">
                                                    <table
                                                        class="w-full
                                                               min-w-[700px]
                                                               border-collapse"
                                                    >
                                                        <thead>
                                                            <tr>
                                                                <th
                                                                    class="border-b
                                                                           border-slate-200
                                                                           px-4 py-3
                                                                           text-left text-xs
                                                                           font-bold uppercase
                                                                           tracking-wide
                                                                           text-slate-500"
                                                                >
                                                                    Action
                                                                </th>
                                                                <th
                                                                    class="border-b
                                                                           border-slate-200
                                                                           px-4 py-3
                                                                           text-left text-xs
                                                                           font-bold uppercase
                                                                           tracking-wide
                                                                           text-slate-500"
                                                                >
                                                                    Description
                                                                </th>
                                                                <th
                                                                    class="w-[100px]
                                                                           border-b
                                                                           border-slate-200
                                                                           px-4 py-3
                                                                           text-center text-xs
                                                                           font-bold uppercase
                                                                           tracking-wide
                                                                           text-slate-500"
                                                                >
                                                                    Allow
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($modulePermissions as $permission)
                                                                <tr
                                                                    class="transition hover:bg-slate-50"
                                                                    @if ($permission->id == 48)
                                                                        id="tr_permission_{{ $permission->id }}"
                                                                        @if (!in_array(7, $selectedPermissions, true))
                                                                            hidden
                                                                        @endif
                                                                    @endif
                                                                >
                                                                    {{-- Action --}}
                                                                    <td
                                                                        class="border-b
                                                                               border-slate-100
                                                                               px-4 py-3"
                                                                    >
                                                                        <div
                                                                            class="text-sm
                                                                                   font-semibold
                                                                                   text-slate-800"
                                                                        >
                                                                            {{ $permission->name }}
                                                                        </div>
                                                                    </td>
                                                                    {{-- Description --}}
                                                                    <td
                                                                        class="border-b
                                                                               border-slate-100
                                                                               px-4 py-3"
                                                                    >
                                                                        <div
                                                                            class="text-sm
                                                                                   text-slate-500"
                                                                        >
                                                                            {{ $permission->description ?: '—' }}
                                                                        </div>
                                                                    </td>
                                                                    {{-- Allow --}}
                                                                    <td
                                                                        class="border-b
                                                                               border-slate-100
                                                                               px-4 py-3
                                                                               text-center"
                                                                    >
                                                                        <label
                                                                            class="relative
                                                                                   inline-flex
                                                                                   cursor-pointer
                                                                                   items-center"
                                                                            for="permission_{{ $permission->id }}"
                                                                        >
                                                                            <input
                                                                                class="peer sr-only"
                                                                                type="checkbox"
                                                                                name="permissions[]"
                                                                                value="{{ $permission->id }}"
                                                                                id="permission_{{ $permission->id }}"
                                                                                @if (
                                                                                    in_array(
                                                                                        (int) $permission->id,
                                                                                        $selectedPermissions,
                                                                                        true
                                                                                    )
                                                                                )
                                                                                    checked
                                                                                @endif
                                                                                @if (
                                                                                    in_array(
                                                                                        (int) $permission->id,
                                                                                        [7, 25, 29, 41, 42],
                                                                                        true
                                                                                    )
                                                                                )
                                                                                    onclick="showSubItems({{ $permission->id }})"
                                                                                @endif
                                                                            >
                                                                            <span
                                                                                class="relative inline-block h-6 w-11
                                                                                       rounded-full
                                                                                       bg-slate-200
                                                                                       transition
                                                                                       peer-checked:bg-sky-600
                                                                                       peer-focus:ring-2
                                                                                       peer-focus:ring-sky-100
                                                                                       after:absolute
                                                                                       after:left-[2px]
                                                                                       after:top-[2px]
                                                                                       after:h-5
                                                                                       after:w-5
                                                                                       after:rounded-full
                                                                                       after:border
                                                                                       after:border-slate-300
                                                                                       after:bg-white
                                                                                after:content-['']
                                                                                       after:transition-all
                                                                                       peer-checked:after:translate-x-5
                                                                                       peer-checked:after:border-white"
                                                                            ></span>
                                                                        </label>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-5">
                                <div
                                    class="flex items-center gap-3
                                           rounded-xl border
                                           border-slate-200
                                           bg-slate-50
                                           px-4 py-4
                                           text-sm text-slate-600"
                                >
                                    <i
                                        class="fa fa-info-circle"
                                        aria-hidden="true"
                                    ></i>
                                    No DIREK permission modules are currently available.
                                </div>
                            </div>
                        @endforelse
                    </div>
                    {{-- Actions --}}
                    <div
                        class="flex flex-col-reverse gap-2
                               border-t border-slate-200
                               bg-slate-50 px-5 py-4
                               sm:flex-row sm:items-center
                               sm:justify-end"
                        id="divBtnSave"
                    >
                        <button
                            type="button"
                            class="btn btn-outline-secondary mb-0"
                            id="btnCancel"
                            onclick="occancel()"
                        >
                            Cancel
                        </button>
                        <button
                            class="btn btn-primary mb-0"
                            type="button"
                            id="btnSave"
                            onclick="ocSubmit()"
                        >
                            <i
                                class="fa fa-save me-1"
                                aria-hidden="true"
                            ></i>
                            Save Changes
                        </button>
                        <button
                            class="btn btn-primary mb-0"
                            type="button"
                            id="btnSaveDisabled"
                            disabled
                            hidden
                        >
                            <span
                                class="spinner-grow
                                       spinner-grow-sm"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            Saving...
                        </button>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            @include('layouts.footers.auth.footer')
        </div>
    </form>
@endsection
@push('css')
<style>
    .role-permission-toggle:focus {
        outline: none;
    }
    .role-permission-toggle-icon {
        transition: transform 0.2s ease;
    }
    .role-permission-panel {
        display: none;
    }
    .role-permission-panel table tbody tr:last-child td {
        border-bottom: 0;
    }
</style>
@endpush
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll(
            '.role-permission-toggle'
        );
        // Permission accordion
        toggles.forEach(function (toggle) {
            toggle.addEventListener(
                'click',
                function () {
                    const targetId =
                        toggle.getAttribute(
                            'data-target'
                        );
                    const panel =
                        document.getElementById(
                            targetId
                        );
                    if (!panel) {
                        return;
                    }
                    const icon =
                        toggle.querySelector(
                            '.role-permission-toggle-icon'
                        );
                    const isOpen =
                        window
                            .getComputedStyle(panel)
                            .display !== 'none';
                    // Close categories
                    document
                        .querySelectorAll(
                            '.role-permission-panel'
                        )
                        .forEach(function (otherPanel) {
                            otherPanel.style.display = 'none';
                        });
                    document
                        .querySelectorAll(
                            '.role-permission-toggle'
                        )
                        .forEach(function (otherToggle) {
                            otherToggle.setAttribute(
                                'aria-expanded',
                                'false'
                            );
                            const otherIcon =
                                otherToggle.querySelector(
                                    '.role-permission-toggle-icon'
                                );
                            if (otherIcon) {
                                otherIcon.classList.remove(
                                    'fa-minus'
                                );
                                otherIcon.classList.add(
                                    'fa-plus'
                                );
                            }
                        });
                    // Open selected category
                    if (!isOpen) {
                        panel.style.display = 'block';
                        toggle.setAttribute(
                            'aria-expanded',
                            'true'
                        );
                        if (icon) {
                            icon.classList.remove(
                                'fa-plus'
                            );
                            icon.classList.add(
                                'fa-minus'
                            );
                        }
                    }
                }
            );
        });
    });
    // Cancel changes
    function occancel() {
        if (
            confirm(
                'Are you sure you want to cancel?'
            ) === true
        ) {
            window.location.href =
                "{{ route('roles.index') }}";
        }
    }
    // Save role
    function ocSubmit() {
        $("#btnSave")
            .attr(
                "hidden",
                true
            );
        $("#btnCancel")
            .attr(
                "disabled",
                true
            );
        $("#btnSaveDisabled")
            .attr(
                "hidden",
                false
            );
        $("#frmUpdate")
            .submit();
    }
</script>
@endpush
