<?php

use CanErdogan\Leaderboard\LeaderboardHandler;
use CanErdogan\Leaderboard\RedisEndpoint;
use Illuminate\Contracts\Foundation\Application;
use Carbon\Carbon;
use Mockery as m;

beforeEach(function () {
    $this->app = m::mock(Application::class);
    $this->handler = new LeaderboardHandler($this->app);
    
    // Access the redisClient property using reflection to reset it for testing
    $reflectionClass = new ReflectionClass(LeaderboardHandler::class);
    $reflectionProperty = $reflectionClass->getProperty('redisClient');
    $reflectionProperty->setAccessible(true);
    
    $this->redisClient = new RedisEndpoint();
    $reflectionProperty->setValue($this->handler, $this->redisClient);
    
    $this->redisClient->flushAll();
});

afterEach(function () {
    $this->redisClient->flushAll();
    m::close();
});

test('it can insert score', function () {
    $result = $this->handler->insertScore('1', 100, [
        'featureId' => 'quiz 1',
        'scoreData' => ['timeTaken' => '1000']
    ]);
    
    expect($result)->toBe(true);
    
    $rank = $this->handler->getRank('1', ['featureId' => 'quiz 1']);
    expect($rank)->toBe(0);
});

test('it can get user best score', function () {
    $this->handler->insertScore('1', 100, [
        'featureId' => 'quiz 1',
        'scoreData' => ['timeTaken' => '1000']
    ]);
    
    $this->handler->insertScore('1', 200, [
        'featureId' => 'quiz 1',
        'scoreData' => ['timeTaken' => '2000']
    ]);
    
    $score = $this->handler->getUserBestScore('1', ['featureId' => 'quiz 1']);
    expect($score)->toBe('200');
});

test('it can get leaderboard', function () {
    $this->handler->insertScore('1', 100, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('2', 200, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('3', 300, ['featureId' => 'quiz 1']);
    
    $leaderboard = $this->handler->getLeaderboard(['featureId' => 'quiz 1']);
    expect($leaderboard)->toBeArray();
    expect($leaderboard)->toHaveCount(3);
    expect($leaderboard[0])->toBe('3'); // User 3 has the highest score
});

test('it can get around me leaderboard', function () {
    $this->handler->insertScore('1', 100, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('2', 200, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('3', 300, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('4', 400, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('5', 500, ['featureId' => 'quiz 1']);
    
    $aroundMe = $this->handler->getAroundMeLeaderboard('3', ['featureId' => 'quiz 1', 'range' => 1]);
    expect($aroundMe)->toBeArray();
    expect($aroundMe)->toHaveCount(3); // User 2, 3, and 4
});

test('it can get rank', function () {
    $this->handler->insertScore('1', 100, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('2', 200, ['featureId' => 'quiz 1']);
    $this->handler->insertScore('3', 300, ['featureId' => 'quiz 1']);
    
    $rank = $this->handler->getRank('2', ['featureId' => 'quiz 1']);
    expect($rank)->toBe(1); // User 2 is at rank 1 (0-indexed)
});

test('it can flush all data', function () {
    $this->handler->insertScore('1', 100, ['featureId' => 'quiz 1']);
    $result = $this->handler->flushAll();
    
    expect($result)->toBe(true);
    
    $rank = $this->handler->getRank('1', ['featureId' => 'quiz 1']);
    expect($rank)->toBeNull();
});

test('it can add leaderboards', function () {
    $this->handler->addLeaderboards(['daily' => true]);
    
    $this->handler->insertScore('1', 100, [
        'featureId' => 'quiz 1',
        'date' => Carbon::now()->toIso8601String()
    ]);
    
    $rank = $this->handler->getRank('1', [
        'leaderboard' => 'daily',
        'featureId' => 'quiz 1'
    ]);
    
    expect($rank)->toBe(0);
});

test('it can remove leaderboards', function () {
    $this->handler->addLeaderboards(['daily' => true]);
    $this->handler->removeLeaderboards(['daily' => true]);
    
    $this->handler->insertScore('1', 100, [
        'featureId' => 'quiz 1',
        'date' => Carbon::now()->toIso8601String()
    ]);
    
    $rank = $this->handler->getRank('1', [
        'leaderboard' => 'daily',
        'featureId' => 'quiz 1'
    ]);
    
    expect($rank)->toBeNull();
});

test('it handles default feature id', function () {
    $this->handler->insertScore('1', 100);
    
    $rank = $this->handler->getRank('1');
    expect($rank)->toBe(0);
});

test('it handles custom date', function () {
    $customDate = Carbon::now()->subDays(5)->toIso8601String();
    
    $this->handler->insertScore('1', 100, [
        'featureId' => 'quiz 1',
        'date' => $customDate
    ]);
    
    $score = $this->handler->getUserBestScore('1', [
        'featureId' => 'quiz 1'
    ], ['date' => true]);
    
    expect($score)->toBeArray();
    expect($score)->toHaveKey('date');
    expect($score['date'])->toBe($customDate);
});