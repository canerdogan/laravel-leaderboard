<?php

use CanErdogan\Leaderboard\RedisEndpoint;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

beforeEach(function () {
    $this->redisClient = new RedisEndpoint();
    $this->redisClient->flushAll();
    $this->redisClient->addPeriodicalLeaderboard('daily');
    
    // Insert some test scores
    $this->redisClient->insertScore('1', 'quiz 1', Carbon::now()->toIso8601String(), 100, ['timeTaken' => '1000']);
    $this->redisClient->insertScore('1', 'quiz 1', Carbon::now()->toIso8601String(), 200, ['timeTaken' => '2000']);
    $this->redisClient->insertScore('2', 'quiz 1', Carbon::now()->toIso8601String(), 200, ['timeTaken' => '3000']);
    $this->redisClient->insertScore('3', 'quiz 1', Carbon::now()->toIso8601String(), 500, ['timeTaken' => '4000']);
    $this->redisClient->insertScore('1', 'quiz 2', Carbon::now()->toIso8601String(), 100, ['timeTaken' => '100']);
});

afterEach(function () {
    $this->redisClient->flushAll();
});

test('it can get all time rank', function () {
    $rank = $this->redisClient->getRank('alltime', '1', 'quiz 1');
    expect($rank)->toBe(1);
});

test('it can get daily rank', function () {
    $rank = $this->redisClient->getRank('daily', '1', 'quiz 1');
    expect($rank)->toBe(1);
});

test('it can get user best score for all time', function () {
    $score = $this->redisClient->getUserBestScore('alltime', '1', 'quiz 1', []);
    expect($score)->toBe('200');
});

test('it can get user best score with score data', function () {
    $score = $this->redisClient->getUserBestScore('alltime', '1', 'quiz 1', ['scoreData' => true]);
    expect($score)->toBeArray();
    expect($score)->toHaveKey('scoreData');
    expect($score)->toHaveKey('rawScore');
});

test('it can get leaderboard', function () {
    $leaderboard = $this->redisClient->getLeaderboard('alltime', 'quiz 1', 0, -1);
    expect($leaderboard)->toBeArray();
    expect($leaderboard)->toHaveCount(3);
    expect($leaderboard[0])->toBe('3'); // User 3 has the highest score (500)
    expect($leaderboard[1])->toBe('2'); // User 2 has the second highest score (200)
    expect($leaderboard[2])->toBe('1'); // User 1 has the third highest score (200, but added later)
});

test('it can get around me leaderboard', function () {
    $aroundMe = $this->redisClient->getAroundMeLeaderboard('1', 'alltime', 'quiz 1', 1);
    expect($aroundMe)->toBeArray();
});

test('it can add periodical leaderboard', function () {
    $this->redisClient->addPeriodicalLeaderboard('weekly');
    $this->redisClient->insertScore('1', 'quiz 1', Carbon::now()->toIso8601String(), 300, ['timeTaken' => '5000']);
    
    $rank = $this->redisClient->getRank('weekly', '1', 'quiz 1');
    expect($rank)->toBe(0);
});

test('it can clear periodical leaderboard', function () {
    $this->redisClient->clearPeriodicalLeaderboard('daily');
    $rank = $this->redisClient->getRank('daily', '1', 'quiz 1');
    expect($rank)->toBeNull();
});

test('it can flush all data', function () {
    $this->redisClient->flushAll();
    $rank = $this->redisClient->getRank('alltime', '1', 'quiz 1');
    expect($rank)->toBeNull();
});