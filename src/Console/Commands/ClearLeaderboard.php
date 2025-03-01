<?php

namespace CanErdogan\Leaderboard\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use CanErdogan\Leaderboard\RedisEndpoint;

class ClearLeaderboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboard:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clearing leaderboards';

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
        $this->info('Clearing leaderboards...');
        
        $redisEndpoint = new RedisEndpoint();
        
        // Clear daily leaderboard
        $redisEndpoint->clearPeriodicalLeaderboard('daily');
        $this->info('Daily leaderboard cleared.');
        
        // Clear weekly leaderboard
        $redisEndpoint->clearPeriodicalLeaderboard('weekly');
        $this->info('Weekly leaderboard cleared.');
        
        // Clear monthly leaderboard
        $redisEndpoint->clearPeriodicalLeaderboard('monthly');
        $this->info('Monthly leaderboard cleared.');
        
        $this->info('All leaderboards have been cleared successfully.');
        
        return 0;
    }
}