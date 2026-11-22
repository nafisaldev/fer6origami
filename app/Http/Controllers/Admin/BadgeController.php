<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\UserLevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->with('roleModel')
            ->withCount(['watchHistories', 'comments', 'reviews', 'watchlists'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'user' => $user,
                    'badges' => UserLevelService::getBadges($user),
                ];
            });

        return view('admin.badges.index', [
            'rows' => $users,
            'catalog' => UserLevelService::badgeCatalog(),
            'search' => $search,
        ]);
    }

    public function edit(User $user): View
    {
        if ($user->isSuperAdmin() && ! Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengatur lencana Super Admin lain.');
        }

        $user->loadCount(['watchHistories', 'comments', 'reviews', 'watchlists']);

        return view('admin.badges.edit', [
            'user' => $user,
            'catalog' => UserLevelService::badgeCatalog(),
            'badges' => UserLevelService::getBadges($user),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->isSuperAdmin() && ! Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengatur lencana Super Admin lain.');
        }

        $data = $request->validate([
            'badges' => ['array'],
            'badges.*' => ['string', Rule::in(array_keys(UserLevelService::badgeCatalog()))],
        ]);

        $manual = UserLevelService::normalizeManualBadges($data['badges'] ?? []);
        $old = UserLevelService::normalizeManualBadges($user->badges);

        $user->update(['badges' => $manual]);

        ActivityLogger::log('Badge', 'updated', "Memperbarui lencana manual {$user->name}", [
            'target_user_id' => $user->id,
            'old_badges' => $old,
            'new_badges' => $manual,
        ]);

        return back()->with('success', "Lencana {$user->name} berhasil diperbarui.");
    }
}
