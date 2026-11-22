@csrf

<div class="grid gap-6 xl:grid-cols-2">
    {{-- BASIC INFORMATION --}}
    <div class="space-y-5">
        <div>
            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Anime <span class="text-brand-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $anime->title) }}" required placeholder="Contoh: Jujutsu Kaisen Season 2" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand-500 focus:ring-brand-500/30">
            @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Sinopsis <span class="text-brand-500">*</span></label>
            <textarea name="synopsis" rows="5" required placeholder="Deskripsi dan alur cerita anime..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand-500 focus:ring-brand-500/30">{{ old('synopsis', $anime->synopsis) }}</textarea>
            @error('synopsis') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Studio Animasi</label>
                <select name="studio_id" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white focus:border-brand-500">
                    <option value="">-- Pilih Studio (Opsional) --</option>
                    @foreach ($studios as $studio)
                        <option value="{{ $studio->id }}" @selected(old('studio_id', $anime->studio_id) == $studio->id)>{{ $studio->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="studio" value="{{ old('studio', $anime->studio) }}" placeholder="Atau tulis nama studio manual" class="mt-2 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white placeholder:text-slate-500">
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Tahun Rilis</label>
                <input type="number" name="year" value="{{ old('year', $anime->year ?: date('Y')) }}" min="1970" max="{{ date('Y') + 2 }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Status</label>
                <select name="status" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2.5 text-xs text-white">
                    @foreach ($statuses as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('status', $anime->status ?: 'ongoing') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Jadwal Rilis</label>
                <select name="release_day" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2.5 text-xs text-white">
                    <option value="">-- Hari Rilis --</option>
                    @foreach ($days as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('release_day', $anime->release_day) === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Rating Umur</label>
                <select name="rating" class="w-full rounded-xl border border-white/10 bg-[#161924] px-3 py-2.5 text-xs text-white">
                    @foreach ($ratings as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('rating', $anime->rating ?: 'PG-13') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Score (0-10)</label>
                <input type="number" step="0.1" min="0" max="10" name="score" value="{{ old('score', $anime->score ?: 8.0) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Tipe</label>
                <input type="text" name="type" value="{{ old('type', $anime->type ?: 'TV') }}" placeholder="TV, Movie, OVA" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Initial Views</label>
                <input type="number" name="views" value="{{ old('views', $anime->views ?: 0) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
            </div>
        </div>

        {{-- GENRES SELECTION --}}
        <div>
            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Genre Anime (Pilih Multi)</label>
            <div class="max-h-36 overflow-y-auto rounded-xl border border-white/10 bg-white/5 p-3 scrollbar-thin">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @php($selectedGenres = old('genre_ids', $anime->genres ? $anime->genres->pluck('id')->all() : []))
                    @foreach ($genres as $genre)
                        <label class="flex items-center gap-2 text-xs text-slate-300 hover:text-white cursor-pointer">
                            <input type="checkbox" name="genre_ids[]" value="{{ $genre->id }}" @checked(in_array($genre->id, $selectedGenres)) class="rounded border-white/10 bg-[#161924] text-brand-500 focus:ring-0">
                            <span>{{ $genre->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- MEDIA & SEO SETTINGS --}}
    <div class="space-y-5">
        {{-- POSTER & BANNER UPLOAD --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-400">Media & Poster Anime</h3>

            <div>
                <label class="mb-1 block text-xs text-slate-300">Poster URL atau Path</label>
                <input type="text" name="poster_path" value="{{ old('poster_path', $anime->poster_path) }}" placeholder="https://..." class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-300">Upload Poster Baru (JPG/PNG/WEBP, Maks 3MB)</label>
                <input type="file" name="poster_upload" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
            </div>

            <div class="pt-2 border-t border-white/5">
                <label class="mb-1 block text-xs text-slate-300">Banner URL atau Path (Cover Lebar)</label>
                <input type="text" name="banner_path" value="{{ old('banner_path', $anime->banner_path) }}" placeholder="https://..." class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-300">Upload Banner Baru (Maks 5MB)</label>
                <input type="file" name="banner_upload" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-white/20">
            </div>
        </div>

        {{-- VISIBILITY BADGES / FEATURED FLAGS --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-400">Pengaturan Homepage & Rekomendasi</h3>

            <div class="grid grid-cols-3 gap-2">
                <label class="flex items-center gap-2 rounded-lg border border-white/5 bg-[#161924] p-2.5 text-xs text-slate-300 hover:border-white/10 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $anime->is_featured)) class="rounded border-white/10 bg-black text-brand-500 focus:ring-0">
                    <span class="font-bold text-white">Featured</span>
                </label>
                <label class="flex items-center gap-2 rounded-lg border border-white/5 bg-[#161924] p-2.5 text-xs text-slate-300 hover:border-white/10 cursor-pointer">
                    <input type="checkbox" name="is_trending" value="1" @checked(old('is_trending', $anime->is_trending)) class="rounded border-white/10 bg-black text-amber-500 focus:ring-0">
                    <span class="font-bold text-white">Trending</span>
                </label>
                <label class="flex items-center gap-2 rounded-lg border border-white/5 bg-[#161924] p-2.5 text-xs text-slate-300 hover:border-white/10 cursor-pointer">
                    <input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $anime->is_popular)) class="rounded border-white/10 bg-black text-blue-500 focus:ring-0">
                    <span class="font-bold text-white">Popular</span>
                </label>
            </div>
        </div>

        {{-- SEO METADATA SETTINGS --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-400">SEO & Optimasi Mesin Pencari</h3>

            <div>
                <label class="mb-1 block text-xs text-slate-300">Custom Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $anime->meta_title) }}" placeholder="Nonton [Judul Anime] Subtitle Indonesia HD" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-300">Meta Description</label>
                <textarea name="meta_description" rows="2" placeholder="Ringkasan singkat untuk hasil pencarian Google..." class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">{{ old('meta_description', $anime->meta_description) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-300">Meta Keywords</label>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $anime->meta_keywords) }}" placeholder="watch anime, anime stream, latest episode, hd" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
            </div>
        </div>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3 border-t border-white/10 pt-5">
    <a href="{{ route('admin.animes.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
        Batal
    </a>
    <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
        Simpan Anime
    </button>
</div>
