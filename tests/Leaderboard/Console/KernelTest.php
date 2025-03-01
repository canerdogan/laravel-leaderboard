<?php

use CanErdogan\Leaderboard\Console\Kernel;
use CanErdogan\Leaderboard\Console\Commands\ClearLeaderboard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

test('kernel registers the clear leaderboard command', function () {
    $app = Mockery::mock(Application::class);
    $events = Mockery::mock(Dispatcher::class);
    
    $kernel = new Kernel($app, $events);
    
    $commands = $kernel->all();
    
    expect($commands)->toBeArray();
    expect($commands)->toContain(ClearLeaderboard::class);
});

test('kernel can handle commands', function () {
    $app = Mockery::mock(Application::class);
    $events = Mockery::mock(Dispatcher::class);
    
    $app->shouldReceive('make')
        ->once()
        ->with(ClearLeaderboard::class)
        ->andReturn(new ClearLeaderboard());
    
    $events->shouldReceive('dispatch')
        ->zeroOrMoreTimes();
    
    $kernel = new Kernel($app, $events);
    
    // This is just to test that the kernel can handle commands without errors
    $result = $kernel->handle(
        new ArrayInput(['command' => 'leaderboard:clear', 'period' => 'daily']),
        new NullOutput()
    );
    
    expect($result)->toBeInt();
});