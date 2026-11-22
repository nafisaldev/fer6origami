<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->query('status');

        $comments = Comment::query()
            ->with(['user', 'anime', 'episode'])
            ->when($search, function ($q) use ($search) {
                $q->where('content', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'));
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'search' => $search,
            'selectedStatus' => $status,
            'statuses' => Comment::STATUSES,
        ]);
    }

    public function edit(Comment $comment): View
    {
        $comment->load(['user', 'anime', 'episode']);

        return view('admin.comments.edit', [
            'comment' => $comment,
            'statuses' => Comment::STATUSES,
        ]);
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'status' => ['required', 'in:' . implode(',', array_keys(Comment::STATUSES))],
        ]);

        $comment->update($data);

        ActivityLogger::log('Comment', 'updated', "Memperbarui komentar ID: {$comment->id} oleh {$comment->user->name}", [
            'comment_id' => $comment->id,
            'status' => $comment->status,
        ]);

        return redirect()->route('admin.comments.index')->with('success', 'Komentar berhasil diperbarui.');
    }

    public function setStatus(Request $request, Comment $comment): RedirectResponse
    {
        $status = $request->input('status');
        if (!in_array($status, array_keys(Comment::STATUSES))) {
            return back()->withErrors(['status' => 'Status komentar tidak valid.']);
        }

        $comment->update(['status' => $status]);

        ActivityLogger::log('Comment', 'status_changed', "Mengubah status komentar ID: {$comment->id} menjadi {$status}", [
            'comment_id' => $comment->id,
            'status' => $status,
        ]);

        return back()->with('success', "Status komentar berhasil diubah menjadi {$status}.");
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $id = $comment->id;
        $userName = $comment->user->name ?? 'User';

        $comment->delete();

        ActivityLogger::log('Comment', 'deleted', "Menghapus komentar ID: {$id} dari {$userName}", [
            'comment_id' => $id,
        ]);

        return redirect()->route('admin.comments.index')->with('success', 'Komentar berhasil dihapus.');
    }
}
