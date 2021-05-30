<?php

namespace App\Console\Commands;

use App\DiscordWebhook;
use App\Models\Portal\User;
use App\Models\Portal\SteamAPI;
use Illuminate\Console\Command;

class PurgeNonMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:purge-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges non-member\'s user accounts.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $passedBag = collect();
        $failedBag = collect();
        $users = User::all()->reject(function ($user) {
            return collect(SteamAPI::members())->contains($user->steam_id);
        });

        foreach ($users as $user) {
            $missions = $user->missions();

            if ($missions->isEmpty()) {
                $user->delete();
                $passedBag->push("**{$user->username}** was purged from ARCHUB as they are no longer in the group.");
                continue;
            }

            $failedBag->push("**{$user->username}** could not be automatically purged from ARCHUB as they have {$missions->count()} mission(s) uploaded.");
        }

        DiscordWebhook::notifyStaff("{$passedBag->implode("\n")}\n\n{$failedBag->implode("\n")}");
    }
}
