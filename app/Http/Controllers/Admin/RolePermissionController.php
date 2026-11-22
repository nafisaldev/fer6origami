<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);
        $allPermissions = Permission::all()->groupBy('module');

        return view('admin.roles.show', [
            'role' => $role,
            'allPermissions' => $allPermissions,
        ]);
    }

    public function create(): View
    {
        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.create', [
            'role' => new Role(),
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $slug = Str::slug($request->input('name'), '_');

        $role = Role::create([
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'is_system' => false,
        ]);

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->input('permission_ids'));
        }

        ActivityLogger::log('Role', 'created', "Membuat role baru: {$role->name}", [
            'role_id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} berhasil dibuat.");
    }

    public function edit(Role $role): View
    {
        $currentUser = Auth::user();

        // Security check: Only Super Admin can edit Super Admin role
        if ($role->slug === 'super_admin' && ! $currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengedit konfigurasi Super Admin.');
        }

        $role->load('permissions');
        $permissions = Permission::all()->groupBy('module');

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $currentUser = Auth::user();

        if ($role->slug === 'super_admin' && ! $currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengedit konfigurasi Super Admin.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        // Don't reduce permissions for super_admin
        if ($role->slug !== 'super_admin') {
            $role->permissions()->sync($request->input('permission_ids', []));
        }

        ActivityLogger::log('Role', 'updated', "Memperbarui role & permissions: {$role->name}", [
            'role_id' => $role->id,
            'permissions_count' => count($request->input('permission_ids', [])),
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Konfigurasi role {$role->name} berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system || in_array($role->slug, ['super_admin', 'admin', 'user'])) {
            return back()->withErrors(['role' => 'Role bawaan sistem tidak dapat dihapus.']);
        }

        if ($role->users()->count() > 0) {
            return back()->withErrors(['role' => "Role {$role->name} masih digunakan oleh {$role->users()->count()} pengguna. Ubah role mereka terlebih dahulu."]);
        }

        $name = $role->name;
        $id = $role->id;

        $role->permissions()->detach();
        $role->delete();

        ActivityLogger::log('Role', 'deleted', "Menghapus role: {$name}", ['role_id' => $id]);

        return redirect()->route('admin.roles.index')->with('success', "Role {$name} berhasil dihapus.");
    }
}
