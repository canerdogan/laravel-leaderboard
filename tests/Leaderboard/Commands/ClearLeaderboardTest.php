<?php

use CanErdogan\Leaderboard\Console\Commands\ClearLeaderboard;
use CanErdogan\Leaderboard\RedisEndpoint;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

beforeEach(function () {
    $this->redisClient = Mockery::mock(RedisEndpoint::class);
    $this->command = new ClearLeaderboard();
    $this->command->setLaravel(app());
});

afterEach(function () {
    Mockery::close();
});

test('command clears daily leaderboard', function () {
    $this->redisClient->shouldReceive('clearPeriodicalLeaderboard')
        ->once()
        ->with('daily')
        ->andReturn(null);
    
    app()->instance(RedisEndpoint::class, $this->redisClient);
    
    $this->command->setInput(new ArrayInput(['period' => 'daily']));
    $this->command->setOutput(new NullOutput());
    
    $result = $this->command->handle();
    
    expect($result)->toBe(Command::SUCCESS);
});

test('command clears weekly leaderboard', function () {
    $this->redisClient->shouldReceive('clearPeriodicalLeaderboard')
        ->once()
        ->with('weekly')
        ->andReturn(null);
    
    app()->instance(RedisEndpoint::class, $this->redisClient);
    
    $this->command->setInput(new ArrayInput(['period' => 'weekly']));
    $this->command->setOutput(new NullOutput());
    
    $result = $this->command->handle();
    
    expect($result)->toBe(Command::SUCCESS);
});

test('command clears monthly leaderboard', function () {
    $this->redisClient->shouldReceive('clearPeriodicalLeaderboard')
        ->once()
        ->with('monthly')
        ->andReturn(null);
    
    app()->instance(RedisEndpoint::class, $this->redisClient);
    
    $this->command->setInput(new ArrayInput(['period' => 'monthly']));
    $this->command->setOutput(new NullOutput());
    
    $result = $this->command->handle();
    
    expect($result)->toBe(Command::SUCCESS);
});

test('command fails with invalid period', function () {
    $this->command->setInput(new ArrayInput(['period' => 'invalid']));
    $this->command->setOutput(new NullOutput());
    
    $result = $this->command->handle();
    
    expect($result)->toBe(Command::FAILURE);
});