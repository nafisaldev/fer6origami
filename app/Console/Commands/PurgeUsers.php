<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PurgeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:purge {--keep= : Email of the user to keep (required)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all user accounts except the specified one (by email).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keepEmail = $this->option('keep');
        if (empty($keepEmail)) {
            $this->error('You must provide an email to keep via --keep option.');
            return 1;
        }

        $targetUser = User::where('email', $keepEmail)->first();
        if (!$targetUser) {
            $this->error("No user found with email '{$keepEmail}'.");
            return 1;
        }
        $targetId = $targetUser->id;

        DB::transaction(function () use ($targetId) {
            // Delete related data for users other than target.
            // Assuming foreign keys are set to cascade, otherwise explicit deletions.
            $relatedTables = [
                'watchlists',
                'comments',
                'reports',
                'activity_logs', // adjust name if exists
                // add other tables referencing users here
            ];
            foreach ($relatedTables as $table) {
                DB::table($table)->where('user_id', '!=', $targetId)->delete();
            }

            // Finally delete the users.
            User::where('id', '!=', $targetId)->delete();
        });

        $remaining = User::count();
        $this->info("Purge completed. Remaining users: {$remaining}");
        return 0;
    }
}
?>