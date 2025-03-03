<?php namespace CanErdogan\Leaderboard;

use CanErdogan\Leaderboard\Console\Kernel;
use Illuminate\Support\ServiceProvider;

class LeaderboardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        // Bootstrap code if needed
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(LeaderboardHandler::class, function($app) {
            return new LeaderboardHandler($app);
        });

        $this->app->singleton('CanErdogan\Leaderboard\Console\Kernel', function($app) {
            $dispatcher = $app->make(\Illuminate\Contracts\Events\Dispatcher::class);
            return new Kernel($app, $dispatcher);
        });

        $this->app->make('CanErdogan\Leaderboard\Console\Kernel');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [LeaderboardHandler::class];
    }
}