<?php

use CanErdogan\Leaderboard\LeaderboardServiceProvider;
use CanErdogan\Leaderboard\LeaderboardHandler;
use CanErdogan\Leaderboard\Facades\Leaderboard;
use Illuminate\Foundation\Application;
use Mockery;

test('service provider registers the leaderboard handler', function () {
    $app = new Application();
    $provider = new LeaderboardServiceProvider($app);
    
    $app->shouldReceive('singleton')
        ->once()
        ->with(LeaderboardHandler::class, Mockery::type('Closure'));
    
    $provider->register();
});

test('service provider registers the facade', function () {
    $app = new Application();
    $provider = new LeaderboardServiceProvider($app);
    
    $app->shouldReceive('singleton')
        ->once()
        ->with(LeaderboardHandler::class, Mockery::type('Closure'));
    
    $app->shouldReceive('booting')
        ->once()
        ->with(Mockery::type('Closure'));
    
    $provider->register();
});

test('service provider provides the correct services', function () {
    $app = new Application();
    $provider = new LeaderboardServiceProvider($app);
    
    $provides = $provider->provides();
    
    expect($provides)->toBeArray();
    expect($provides)->toContain(LeaderboardHandler::class);
});