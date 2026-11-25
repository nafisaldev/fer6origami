@props([
    'comments' => collect(),
    'commentStyles' => collect(),
    'defaultCommentStyle' => null,
    'animeId' => null,
    'episodeId' => null,
    'title' => 'Comments & Community Discussion',
    'placeholder' => 'Share your thoughts...',
])

@php
    $totalCommentCount = $comments->count() + $comments->sum(fn ($comment) => $comment->replies->count());
@endphp
<div class="surface-panel rounded-xl p-5">
    <div class="mb-4 flex items-center justify-between border-b border-white/5 pb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $title }}</span>
        <span class="text-xs text-slate-400">{{ $totalCommentCount }} {{ Str::plural('Comment', $totalCommentCount) }}</span>
    </div>

    @if ($animeId || $episodeId)
        @auth
            <form action="{{ route('comments.store') }}" method="POST" class="mb-6 space-y-3">
                @csrf
                @if ($animeId)
                    <input type="hidden" name="anime_id" value="{{ $animeId }}">
                @endif
                @if ($episodeId)
                    <input type="hidden" name="episode_id" value="{{ $episodeId }}">
                @endif
                <textarea
                    name="content"
                    rows="3"
                    required
                    placeholder="{{ $placeholder }}"
                    class="w-full rounded-xl border border-white/10 bg-[#11131b] p-3 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                ></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-brand-600">
                        Post Comment
                    </button>
                </div>
            </form>
        @else
            <div class="mb-6 rounded-xl border border-white/5 bg-[#161924] p-4 text-center text-xs text-slate-400">
                Please <a href="{{ route('login') }}" class="font-bold text-brand-400 hover:underline">Log In</a> to join the conversation.
            </div>
        @endauth
    @else
        <div class="mb-6 rounded-xl border border-white/5 bg-[#161924] p-4 text-center text-xs text-slate-400">
            Comments are available after this anime is added to the catalog.
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($comments as $comment)
            @php
                $style = $commentStyles->get($comment->user_id) ?? $defaultCommentStyle;
            @endphp
            <div class="rounded-xl border p-3.5 {{ $style['comment_card'] ?? 'border-white/5 bg-[#161924]' }}" id="comment-{{ $comment->id }}">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $style['comment_avatar'] ?? 'bg-white/10 text-slate-300' }}">
                            {{ strtoupper(substr($comment->user->name ?? 'User', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex min-w-0 items-center gap-1.5">
                                <span class="truncate text-xs font-bold {{ $style['comment_name'] ?? 'text-white' }}">{{ $comment->user->name ?? 'Community Member' }}</span>
                                @if (($style['tier'] ?? 'free') !== 'free')
                                    <span class="shrink-0 rounded border px-1.5 py-0.5 text-[9px] font-extrabold uppercase {{ $style['comment_badge'] ?? '' }}">{{ $style['label'] ?? '' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="shrink-0 text-[10px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="mt-2 text-xs leading-relaxed text-slate-200">{{ $comment->content }}</p>

                <div class="mt-2 flex items-center gap-3">
                    @auth
                        <button
                            type="button"
                            class="text-[11px] font-semibold text-brand-400 transition hover:text-brand-300"
                            onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('hidden')"
                        >
                            Reply
                        </button>
                    @endauth
                    @if ($comment->replies->count())
                        <span class="text-[10px] text-slate-500">{{ $comment->replies->count() }} {{ Str::plural('reply', $comment->replies->count()) }}</span>
                    @endif
                </div>

                @auth
                    <form id="reply-form-{{ $comment->id }}" action="{{ route('comments.store') }}" method="POST" class="mt-3 hidden space-y-2">
                        @csrf
                        @if ($animeId)
                            <input type="hidden" name="anime_id" value="{{ $animeId }}">
                        @endif
                        @if ($episodeId)
                            <input type="hidden" name="episode_id" value="{{ $episodeId }}">
                        @endif
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                        <textarea
                            name="content"
                            rows="2"
                            required
                            placeholder="Write a reply..."
                            class="w-full rounded-xl border border-white/10 bg-[#11131b] p-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500 focus:outline-none"
                        ></textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.add('hidden')" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-400 transition hover:text-white">Cancel</button>
                            <button type="submit" class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-brand-600">Reply</button>
                        </div>
                    </form>
                @endauth

                @if ($comment->replies->count())
                    <div class="mt-3 space-y-2 border-l border-white/10 pl-3 sm:pl-4">
                        @foreach ($comment->replies->sortBy('created_at') as $reply)
                            @php
                                $replyStyle = $commentStyles->get($reply->user_id) ?? $defaultCommentStyle;
                            @endphp
                            <div class="rounded-lg border p-3 {{ $replyStyle['comment_card'] ?? 'border-white/5 bg-[#11131b]' }}" id="comment-{{ $reply->id }}">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold {{ $replyStyle['comment_avatar'] ?? 'bg-white/10 text-slate-300' }}">
                                            {{ strtoupper(substr($reply->user->name ?? 'User', 0, 1)) }}
                                        </div>
                                        <div class="flex min-w-0 items-center gap-1.5">
                                            <span class="truncate text-xs font-bold {{ $replyStyle['comment_name'] ?? 'text-white' }}">{{ $reply->user->name ?? 'Community Member' }}</span>
                                            @if (($replyStyle['tier'] ?? 'free') !== 'free')
                                                <span class="shrink-0 rounded border px-1.5 py-0.5 text-[9px] font-extrabold uppercase {{ $replyStyle['comment_badge'] ?? '' }}">{{ $replyStyle['label'] ?? '' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-[10px] text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-2 text-xs leading-relaxed text-slate-200">{{ $reply->content }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="py-4 text-center text-xs text-slate-500">No comments yet. Be the first to share your thoughts!</p>
        @endforelse
    </div>
</div>
