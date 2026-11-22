<?php

namespace App\Console\Commands;

use App\Models\DeletedAnime;
use App\Services\DeletedAnimeRestoreService;
use Illuminate\Console\Command;

class RestoreDeletedAnime extends Command
{
    protected $signature = 'anime:restore-deleted
        {--slug=* : Pulihkan hanya slug yang ditentukan}
        {--keep-blacklist : Biarkan entri blacklist setelah anime dipulihkan}';

    protected $description = 'Memulihkan anime yang terhapus dari blacklist ke katalog lokal.';

    public function handle(DeletedAnimeRestoreService $restorer): int
    {
        $query = DeletedAnime::query()->orderBy('id');
        $slugs = array_filter($this->option('slug'));

        if ($slugs !== []) {
            $query->whereIn('slug', $slugs);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada anime di blacklist untuk dipulihkan.');

            return self::SUCCESS;
        }

        $restored = 0;
        $existing = 0;
        $failed = 0;

        foreach ($records as $record) {
            $result = $restorer->restore($record, ! $this->option('keep-blacklist'));

            if ($result['status'] === 'restored') {
                $restored++;
                $this->info('OK   ' . $result['anime']->title);
            } elseif ($result['status'] === 'exists') {
                $existing++;
                $this->line('LEWAT ' . $record->title . ': ' . $result['message']);
            } else {
                $failed++;
                $this->warn('GAGAL ' . $record->title . ': ' . $result['message']);
            }
        }

        $this->info("Selesai. Dipulihkan: {$restored}. Sudah ada: {$existing}. Gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
