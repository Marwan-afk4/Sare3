<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', __('Role created successfully.'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role)
    {
        // Prevent deleting critical system roles
        if (in_array($role->name, ['admin', 'super-admin'], true)) {
            return redirect()->route('roles.index')
                ->with('error', __('The admin role is a system role and cannot be deleted.'));
        }

        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', __('Cannot delete role with assigned users. Please reassign users first.'));
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', __('Role deleted successfully.'));
    }
}
