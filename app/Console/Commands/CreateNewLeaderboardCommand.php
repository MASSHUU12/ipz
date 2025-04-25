<?php

namespace App\Console\Commands;

use App\Jobs\CreateNewLeaderboard;
use Illuminate\Console\Command;

class CreateNewLeaderboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'airpollution:leaderboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the CreateNewLeaderboard job';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        CreateNewLeaderboard::dispatch();

        $this->info('CreateNewLeaderboard job has been dispatched.');
    }
}
