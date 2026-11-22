<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Report;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserInteractionController extends Controller
{
    public function storeComment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content' => ['nullable', 'string', 'max:2000'],
            'body' => ['nullable', 'string', 'max:2000'],
            'anime_id' => ['nullable', 'integer', 'exists:animes,id'],
            'episode_id' => ['nullable', 'integer', 'exists:episodes,id'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $commentText = $data['content'] ?? ($data['body'] ?? '');
        if (blank($commentText)) {
            return back()->withErrors(['content' => 'Comment cannot be empty.']);
        }

        Comment::create([
            'user_id' => Auth::id(),
            'anime_id' => $data['anime_id'] ?? null,
            'episode_id' => $data['episode_id'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $commentText,
            'status' => 'published',
        ]);

        return back()->with('success', 'Your comment has been posted successfully!');
    }

    public function storeReview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'anime_id' => ['required', 'integer', 'exists:animes,id'],
            'rating' => ['required', 'numeric', 'min:1', 'max:10'],
            'review_text' => ['nullable', 'string', 'max:3000'],
            'content' => ['nullable', 'string', 'max:3000'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $review = Review::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'anime_id' => $data['anime_id'],
            ],
            [
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'content' => $data['content'] ?? ($data['review_text'] ?? null),
                'status' => 'published',
            ]
        );

        // Update anime average score
        $anime = Anime::find($data['anime_id']);
        if ($anime) {
            $avgScore = Review::where('anime_id', $anime->id)->where('status', 'published')->avg('rating');
            $anime->update(['score' => round($avgScore, 1)]);
        }

        return back()->with('success', 'Your rating and review have been submitted!');
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(Report::TYPES))],
            'description' => ['required', 'string', 'max:2000'],
            'reportable_type' => ['nullable', 'string', 'max:255'],
            'reportable_id' => ['nullable', 'integer'],
        ]);

        Report::create([
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'description' => $data['description'],
            'reportable_type' => $data['reportable_type'] ?? null,
            'reportable_id' => $data['reportable_id'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thank you! Your report has been submitted to the admin team.');
    }

    public function markNotificationsRead(): RedirectResponse
    {
        if (Auth::check()) {
            $notifs = \App\Models\BroadcastNotification::query()
                ->where(fn ($q) => $q->whereNull('target_role')->orWhere('target_role', Auth::user()->role ?? 'user'))
                ->pluck('id');

            foreach ($notifs as $id) {
                \Illuminate\Support\Facades\DB::table('user_notification_reads')->updateOrInsert(
                    ['notification_id' => $id, 'user_id' => Auth::id()],
                    ['read_at' => now()]
                );
            }
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
