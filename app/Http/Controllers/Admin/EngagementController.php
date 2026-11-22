<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserEngagementService;
use App\Services\UserLevelService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $filter = $request->query('quest', 'all');

        if (! in_array($filter, ['all', 'progress', 'claimable', 'claimed'], true)) {
            $filter = 'all';
        }

        $weekStart = now()->startOfWeek()->toDateString();
        $engagement = new UserEngagementService();

        $users = User::query()
            ->select('users.*')
            ->with('roleModel')
            ->withCount(['watchHistories as weekly_watches_count' => fn ($q) => $q->where('created_at', '>=', $weekStart)])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('users.name', 'like', '%' . $search . '%')
                        ->orWhere('users.email', 'like', '%' . $search . '%');
                });
            })
            ->when($filter === 'claimable', fn ($q) => $q
                ->having('weekly_watches_count', '>=', UserEngagementService::WEEKLY_TARGET)
                ->whereDoesntHave('weeklyQuests', fn ($quest) => $quest
                    ->where('week_start', $weekStart)
                    ->whereNotNull('claimed_at')))
            ->when($filter === 'progress', fn ($q) => $q
                ->having('weekly_watches_count', '<', UserEngagementService::WEEKLY_TARGET)
                ->whereDoesntHave('weeklyQuests', fn ($quest) => $quest
                    ->where('week_start', $weekStart)
                    ->whereNotNull('claimed_at')))
            ->when($filter === 'claimed', fn ($q) => $q->whereHas('weeklyQuests', fn ($quest) => $quest
                ->where('week_start', $weekStart)
                ->whereNotNull('claimed_at')))
            ->latest('users.created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(function (User $user) use ($engagement) {
                return [
                    'user' => $user,
                    'levelInfo' => UserLevelService::getLevelDetails($user->xp),
                    'streak' => $engagement->streak($user),
                    'quest' => $engagement->weeklyQuest($user),
                ];
            });

        return view('admin.engagement.index', [
            'rows' => $users,
            'search' => $search,
            'selectedQuest' => $filter,
        ]);
    }
}
