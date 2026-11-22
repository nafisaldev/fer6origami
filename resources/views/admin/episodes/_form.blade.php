@csrf

<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-2">
        {{-- EPISODE BASICS --}}
        <div class="space-y-5">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Pilih Anime <span class="text-brand-500">*</span></label>
                <select name="anime_id" required class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white focus:border-brand-500">
                    <option value="">-- Pilih Anime --</option>
                    @foreach ($animes as $animeOption)
                        <option value="{{ $animeOption->id }}" @selected((string) old('anime_id', $episode->anime_id) === (string) $animeOption->id)>
                            {{ $animeOption->title }}
                        </option>
                    @endforeach
                </select>
                @error('anime_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Judul Episode <span class="text-brand-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $episode->title) }}" required placeholder="Contoh: Pertemuan di Shibuya" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand-500">
                @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Nomor Episode <span class="text-brand-500">*</span></label>
                    <input type="number" name="episode_number" min="1" value="{{ old('episode_number', $episode->episode_number ?: 1) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:border-brand-500">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Durasi (Menit)</label>
                    <input type="number" name="duration_minutes" min="1" value="{{ old('duration_minutes', $episode->duration_minutes ?: 24) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Sinopsis Episode</label>
                <textarea name="synopsis" rows="4" placeholder="Ringkasan episode ini (opsional)..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">{{ old('synopsis', $episode->synopsis) }}</textarea>
            </div>
        </div>

        {{-- VIDEO & STREAMING BASICS --}}
        <div class="space-y-5">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Link Video Utama (Direct MP4 / Embed Player)</label>
                <input type="url" name="video_url" value="{{ old('video_url', $episode->video_url) }}" placeholder="https://..." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white placeholder:text-slate-500 focus:border-brand-500">
                <p class="mt-1 text-[11px] text-slate-500">Mendukung link MP4 langsung, HLS/m3u8, atau URL iframe embed.</p>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Jadwal / Tanggal Tayang</label>
                <input type="datetime-local" name="aired_at" value="{{ old('aired_at', optional($episode->aired_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Akses Minimum</label>
                    <select name="minimum_plan" class="w-full rounded-xl border border-white/10 bg-[#161924] px-4 py-2.5 text-xs text-white focus:border-brand-500">
                        <option value="" @selected(old('minimum_plan', $episode->minimum_plan) === null || old('minimum_plan', $episode->minimum_plan) === '')>Free</option>
                        <option value="premium" @selected(old('minimum_plan', $episode->minimum_plan) === 'premium')>Premium</option>
                        <option value="vip" @selected(old('minimum_plan', $episode->minimum_plan) === 'vip')>VIP</option>
                    </select>
                    <p class="mt-1 text-[11px] text-slate-500">Paket minimum untuk menonton episode ini.</p>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-300">Akses Awal Hingga</label>
                    <input type="datetime-local" name="early_access_until" value="{{ old('early_access_until', optional($episode->early_access_until)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs text-white">
                    <p class="mt-1 text-[11px] text-slate-500">Kosongkan jika tidak ada masa akses awal.</p>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-4 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $episode->is_published ?? true)) class="rounded border-white/10 bg-[#161924] text-brand-500 focus:ring-0">
                    <div>
                        <span class="font-bold text-white block">Publikasikan Episode Ini</span>
                        <span class="text-slate-400 text-[11px]">Episode langsung dapat ditonton oleh publik jika dicentang.</span>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- ADVANCED: MULTIPLE STREAMING SERVERS REPEATER --}}
    <div class="surface-card rounded-2xl border border-white/10 p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-white/5 pb-2">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-400">Pilihan Server Streaming Tambahan</h3>
                <p class="text-[11px] text-slate-400">Sediakan opsi server alternatif jika link utama mengalami kendala.</p>
            </div>
            <button type="button" onclick="addServerRow()" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-bold text-white hover:bg-white/20">
                + Tambah Server
            </button>
        </div>

        <div id="serversContainer" class="space-y-3">
            @php
                $existingServers = old('server_names') ? [] : ($episode->servers ?: []);
                if (old('server_names')) {
                    foreach (old('server_names') as $i => $sname) {
                        $existingServers[] = [
                            'name' => $sname,
                            'url' => old('server_urls')[$i] ?? '',
                            'quality' => old('server_qualities')[$i] ?? '720p',
                        ];
                    }
                }
            @endphp

            @forelse ($existingServers as $s)
                <div class="grid grid-cols-12 gap-2 server-row items-center">
                    <div class="col-span-3">
                        <input type="text" name="server_names[]" value="{{ $s['name'] ?? '' }}" placeholder="Nama Server (cth: FastDrive)" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
                    </div>
                    <div class="col-span-6">
                        <input type="text" name="server_urls[]" value="{{ $s['url'] ?? '' }}" placeholder="URL Streaming (embed/direct)" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
                    </div>
                    <div class="col-span-2">
                        <select name="server_qualities[]" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-2 py-2 text-xs text-white">
                            <option value="1080p" @selected(($s['quality'] ?? '') === '1080p')>1080p HD</option>
                            <option value="720p" @selected(($s['quality'] ?? '720p') === '720p')>720p HD</option>
                            <option value="480p" @selected(($s['quality'] ?? '') === '480p')>480p SD</option>
                            <option value="360p" @selected(($s['quality'] ?? '') === '360p')>360p Low</option>
                        </select>
                    </div>
                    <div class="col-span-1 text-center">
                        <button type="button" onclick="this.closest('.server-row').remove()" class="text-red-400 hover:text-red-300 text-xs font-bold">Hapus</button>
                    </div>
                </div>
            @empty
                <div class="grid grid-cols-12 gap-2 server-row items-center">
                    <div class="col-span-3">
                        <input type="text" name="server_names[]" value="Server 1 (Primary)" placeholder="Nama Server" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
                    </div>
                    <div class="col-span-6">
                        <input type="text" name="server_urls[]" value="" placeholder="URL Streaming (embed/mp4)" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
                    </div>
                    <div class="col-span-2">
                        <select name="server_qualities[]" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-2 py-2 text-xs text-white">
                            <option value="1080p">1080p HD</option>
                            <option value="720p" selected>720p HD</option>
                            <option value="480p">480p SD</option>
                            <option value="360p">360p Low</option>
                        </select>
                    </div>
                    <div class="col-span-1 text-center">
                        <button type="button" onclick="this.closest('.server-row').remove()" class="text-red-400 hover:text-red-300 text-xs font-bold">Hapus</button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3 border-t border-white/10 pt-5">
    <a href="{{ route('admin.episodes.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
        Batal
    </a>
    <button type="submit" class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-bold text-white shadow-glow transition hover:bg-brand-600">
        Simpan Episode
    </button>
</div>

<script>
    function addServerRow() {
        const container = document.getElementById('serversContainer');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-2 server-row items-center';
        div.innerHTML = `
            <div class="col-span-3">
                <input type="text" name="server_names[]" placeholder="Nama Server Baru" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
            </div>
            <div class="col-span-6">
                <input type="text" name="server_urls[]" placeholder="URL Streaming" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-3 py-2 text-xs text-white">
            </div>
            <div class="col-span-2">
                <select name="server_qualities[]" class="w-full rounded-xl border border-white/10 bg-[#11131b] px-2 py-2 text-xs text-white">
                    <option value="1080p">1080p HD</option>
                    <option value="720p" selected>720p HD</option>
                    <option value="480p">480p SD</option>
                    <option value="360p">360p Low</option>
                </select>
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.server-row').remove()" class="text-red-400 hover:text-red-300 text-xs font-bold">Hapus</button>
            </div>
        `;
        container.appendChild(div);
    }
</script>
