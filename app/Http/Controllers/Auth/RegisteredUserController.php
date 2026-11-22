<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (! WebsiteSetting::get('allow_registration', true)) {
            return redirect()->route('login')->withErrors(['email' => 'Pendaftaran akun baru saat ini sedang dinonaktifkan oleh administrator.']);
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! WebsiteSetting::get('allow_registration', true)) {
            return redirect()->route('login')->withErrors(['email' => 'Pendaftaran akun baru saat ini sedang dinonaktifkan oleh administrator.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $defaultRoleSlug = WebsiteSetting::get('default_role', 'user');
        $roleModel = Role::where('slug', $defaultRoleSlug)->first();

        $user = User::create($data + [
            'role' => $defaultRoleSlug,
            'role_id' => $roleModel?->id,
            'status' => 'active',
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        ActivityLogger::log('Auth', 'registered', "Pengguna baru {$user->name} ({$user->email}) mendaftar akun.", null, $user->id);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Registrasi berhasil. Selamat datang di Fer6origami.');
    }
}
