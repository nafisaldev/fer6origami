<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\UserLevelService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');

        $users = User::query()
            ->with('roleModel')
            ->withCount(['watchlists', 'comments', 'watchHistories'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($roleFilter, function ($q) use ($roleFilter) {
                $q->where('role', $roleFilter)
                    ->orWhereHas('roleModel', fn ($r) => $r->where('slug', $roleFilter));
            })
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'selectedRole' => $roleFilter,
            'selectedStatus' => $statusFilter,
            'roles' => Role::all(),
            'statuses' => User::STATUSES,
        ]);
    }

    public function show(User $user): View
    {
        $user->load([
            'roleModel',
            'watchlists.anime',
            'watchHistories' => fn ($q) => $q->with(['anime', 'episode'])->latest()->take(20),
            'comments' => fn ($q) => $q->with(['anime', 'episode'])->latest()->take(20),
            'reviews.anime',
            'reports' => fn ($q) => $q->latest()->take(10),
        ]);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): View
    {
        $currentUser = Auth::user();

        // Security check: Only Super Admin can edit another Super Admin
        if ($user->isSuperAdmin() && ! $currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengedit data Super Admin lain.');
        }

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::all(),
            'statuses' => User::STATUSES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        // Security check: Only Super Admin can edit another Super Admin
        if ($user->isSuperAdmin() && ! $currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengedit data Super Admin lain.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['nullable', 'exists:roles,id'],
            'status' => ['required', Rule::in(array_keys(User::STATUSES))],
            'banned_reason' => ['nullable', 'string', 'max:255'],
            'banned_until' => ['nullable', 'date'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Security check: Only Super Admin can grant Super Admin role
        if (!empty($data['role_id'])) {
            $selectedRole = Role::find($data['role_id']);
            if ($selectedRole?->slug === 'super_admin' && ! $currentUser->isSuperAdmin()) {
                abort(403, 'Hanya Super Admin yang dapat memberikan hak akses Super Admin.');
            }
            $data['role'] = $selectedRole?->slug ?? 'user';
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $oldRole = $user->role;
        $oldStatus = $user->status;

        $user->update($data);

        ActivityLogger::log('User', 'updated', "Memperbarui profil user {$user->name} ({$user->email})", [
            'target_user_id' => $user->id,
            'old_role' => $oldRole,
            'new_role' => $user->role,
            'old_status' => $oldStatus,
            'new_status' => $user->status,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function updateLevel(Request $request, User $user): RedirectResponse
    {
        if ($user->isSuperAdmin() && ! Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengatur level Super Admin lain.');
        }

        $data = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:50'],
            'auto_sync' => ['nullable', 'boolean'],
        ]);

        $level = (int) $data['level'];
        $xp = UserLevelService::getXpForLevel($level);
        $autoSync = $request->boolean('auto_sync');
        $oldLevel = $user->level;
        $oldXp = $user->xp;
        $oldMode = $user->xp_manual ? 'manual' : 'auto';

        $user->update([
            'xp' => $xp,
            'xp_manual' => ! $autoSync,
        ]);

        if ($autoSync) {
            $user->refresh();
            UserLevelService::syncUserXp($user);
        }

        $user->refresh();

        ActivityLogger::log('User', 'level_updated', "Mengatur level {$user->name} menjadi Lv. {$user->level}", [
            'target_user_id' => $user->id,
            'old_level' => $oldLevel,
            'new_level' => $user->level,
            'old_xp' => $oldXp,
            'new_xp' => $user->xp,
            'old_mode' => $oldMode,
            'new_mode' => $user->xp_manual ? 'manual' : 'auto',
        ]);

        $modeNote = $user->xp_manual
            ? 'Sinkronisasi otomatis dimatikan.'
            : 'Sinkronisasi otomatis dari aktivitas diaktifkan.';

        return back()->with('success', "Level {$user->name} diset ke Lv. {$user->level}. {$modeNote}");
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat memblokir akun Anda sendiri.']);
        }

        if ($user->isSuperAdmin()) {
            return back()->withErrors(['user' => 'Super Admin tidak dapat diblokir.']);
        }

        $request->validate([
            'status' => ['required', Rule::in(['suspended', 'banned'])],
            'banned_reason' => ['required', 'string', 'max:255'],
            'banned_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $bannedUntil = null;
        if ($request->input('status') === 'suspended' && $request->filled('banned_days')) {
            $bannedUntil = Carbon::now()->addDays((int) $request->input('banned_days'));
        }

        $user->update([
            'status' => $request->input('status'),
            'banned_reason' => $request->input('banned_reason'),
            'banned_until' => $bannedUntil,
        ]);

        ActivityLogger::log('User', 'banned', "Memberikan sanksi {$request->input('status')} pada user {$user->name}", [
            'target_user_id' => $user->id,
            'reason' => $request->input('banned_reason'),
            'banned_until' => $bannedUntil,
        ]);

        return back()->with('success', "User {$user->name} berhasil disanksi ({$request->input('status')}).");
    }

    public function unban(User $user): RedirectResponse
    {
        $user->update([
            'status' => 'active',
            'banned_reason' => null,
            'banned_until' => null,
        ]);

        ActivityLogger::log('User', 'unbanned', "Membuka blokir/sanksi untuk user {$user->name}", [
            'target_user_id' => $user->id,
        ]);

        return back()->with('success', "Status akun {$user->name} berhasil dipulihkan menjadi aktif.");
    }

    public function toggleVerify(User $user): RedirectResponse
    {
        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
            $msg = "Status verifikasi {$user->name} dinonaktifkan.";
        } else {
            $user->update(['email_verified_at' => now()]);
            $msg = "Akun {$user->name} berhasil diverifikasi.";
        }

        ActivityLogger::log('User', 'verification_toggled', $msg, ['target_user_id' => $user->id]);

        return back()->with('success', $msg);
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($user->isSuperAdmin() && ! $currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak menghapus akun Super Admin.');
        }

        $userName = $user->name;
        $userId = $user->id;

        $user->delete();

        ActivityLogger::log('User', 'deleted', "Menghapus akun pengguna {$userName} (ID: {$userId})", [
            'deleted_user_id' => $userId,
            'name' => $userName,
        ]);

        return redirect()->route('admin.users.index')->with('success', "Akun {$userName} berhasil dihapus.");
    }
}
