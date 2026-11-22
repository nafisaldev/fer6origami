<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Review;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->query('status');
        $rating = $request->query('rating');

        $reviews = Review::query()
            ->with(['user', 'anime'])
            ->when($search, function ($q) use ($search) {
                $q->where('content', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('anime', fn ($a) => $a->where('title', 'like', '%' . $search . '%'));
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($rating, fn ($q) => $q->where('rating', $rating))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedRating' => $rating,
            'statuses' => Review::STATUSES,
        ]);
    }

    public function setStatus(Request $request, Review $review): RedirectResponse
    {
        $status = $request->input('status');
        if (!in_array($status, array_keys(Review::STATUSES))) {
            return back()->withErrors(['status' => 'Status review tidak valid.']);
        }

        $review->update(['status' => $status]);

        // Recalculate average score for anime if review status changed
        $this->syncAnimeScore($review->anime);

        ActivityLogger::log('Review', 'status_changed', "Mengubah status review ID: {$review->id} menjadi {$status}", [
            'review_id' => $review->id,
            'anime_id' => $review->anime_id,
            'status' => $status,
        ]);

        return back()->with('success', "Status review berhasil diubah menjadi {$status}.");
    }

    public function destroy(Review $review): RedirectResponse
    {
        $id = $review->id;
        $anime = $review->anime;

        $review->delete();

        if ($anime) {
            $this->syncAnimeScore($anime);
        }

        ActivityLogger::log('Review', 'deleted', "Menghapus review ID: {$id}", ['review_id' => $id]);

        return redirect()->route('admin.reviews.index')->with('success', 'Review berhasil dihapus.');
    }

    private function syncAnimeScore(?Anime $anime): void
    {
        if (! $anime) return;

        $avgScore = Review::where('anime_id', $anime->id)
            ->where('status', 'published')
            ->avg('rating');

        if ($avgScore) {
            $anime->update(['score' => round($avgScore, 1)]);
        }
    }
}
