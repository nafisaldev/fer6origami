<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipHistory;
use App\Models\UserMembership;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', UserMembership::class);

        $search = $request->string('search')->toString();
        $action = $request->string('action')->toString();

        $histories = MembershipHistory::query()
            ->with(['user', 'fromPlan', 'toPlan', 'actor'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%'));
            })
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.membership-history.index', [
            'histories' => $histories,
            'search' => $search,
            'selectedAction' => $action,
        ]);
    }
}
