<?php

use CanErdogan\Leaderboard\Facades\Leaderboard;
use CanErdogan\Leaderboard\LeaderboardHandler;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

beforeEach(function () {
    // Mock the LeaderboardHandler
    $this->handler = Mockery::mock(LeaderboardHandler::class);
    App::instance(LeaderboardHandler::class, $this->handler);
});

afterEach(function () {
    Mockery::close();
});

test('facade can insert score', function () {
    $userId = '1';
    $rawScore = 100;
    $options = ['featureId' => 'quiz 1'];
    
    $this->handler->shouldReceive('insertScore')
        ->once()
        ->with($userId, $rawScore, $options)
        ->andReturn(true);
    
    $result = Leaderboard::insertScore($userId, $rawScore, $options);
    expect($result)->toBe(true);
});

test('facade can get user best score', function () {
    $userId = '1';
    $getOptions = ['featureId' => 'quiz 1'];
    $returnOptions = ['scoreData' => true];
    $expectedScore = ['rawScore' => 100, 'scoreData' => ['timeTaken' => '1000']];
    
    $this->handler->shouldReceive('getUserBestScore')
        ->once()
        ->with($userId, $getOptions, $returnOptions)
        ->andReturn($expectedScore);
    
    $score = Leaderboard::getUserBestScore($userId, $getOptions, $returnOptions);
    expect($score)->toBe($expectedScore);
});

test('facade can get leaderboard', function () {
    $options = ['featureId' => 'quiz 1'];
    $expectedLeaderboard = ['3', '2', '1'];
    
    $this->handler->shouldReceive('getLeaderboard')
        ->once()
        ->with($options)
        ->andReturn($expectedLeaderboard);
    
    $leaderboard = Leaderboard::getLeaderboard($options);
    expect($leaderboard)->toBe($expectedLeaderboard);
});

test('facade can get around me leaderboard', function () {
    $userId = '3';
    $options = ['featureId' => 'quiz 1', 'range' => 1];
    $expectedAroundMe = ['2', '3', '4'];
    
    $this->handler->shouldReceive('getAroundMeLeaderboard')
        ->once()
        ->with($userId, $options)
        ->andReturn($expectedAroundMe);
    
    $aroundMe = Leaderboard::getAroundMeLeaderboard($userId, $options);
    expect($aroundMe)->toBe($expectedAroundMe);
});

test('facade can get rank', function () {
    $userId = '2';
    $options = ['featureId' => 'quiz 1'];
    $expectedRank = 1;
    
    $this->handler->shouldReceive('getRank')
        ->once()
        ->with($userId, $options)
        ->andReturn($expectedRank);
    
    $rank = Leaderboard::getRank($userId, $options);
    expect($rank)->toBe($expectedRank);
});

test('facade can flush all data', function () {
    $this->handler->shouldReceive('flushAll')
        ->once()
        ->andReturn(true);
    
    $result = Leaderboard::flushAll();
    expect($result)->toBe(true);
});

test('facade can add leaderboards', function () {
    $options = ['daily' => true];
    
    $this->handler->shouldReceive('addLeaderboards')
        ->once()
        ->with($options)
        ->andReturn(null);
    
    Leaderboard::addLeaderboards($options);
    // No assertion needed, just verifying the method was called
});

test('facade can remove leaderboards', function () {
    $options = ['daily' => true];
    
    $this->handler->shouldReceive('removeLeaderboards')
        ->once()
        ->with($options)
        ->andReturn(null);
    
    Leaderboard::removeLeaderboards($options);
    // No assertion needed, just verifying the method was called
});