<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CanErdogan\Leaderboard\LeaderboardHandler;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Redis;
use Illuminate\Redis\RedisManager;
use Illuminate\Config\Repository;

// Bootstrap Laravel components
$app = new Container();
$app->singleton('config', function () {
    $config = new Repository();
    
    // Configure Redis
    $config->set('database.redis', [
        'client' => 'predis',
        'default' => [
            'host' => '127.0.0.1',
            'password' => null,
            'port' => 6379,
            'database' => 0,
        ],
    ]);
    
    return $config;
});

// Set up Redis
$app->singleton('redis', function ($app) {
    $config = $app->make('config')->get('database.redis');
    return new RedisManager($app, 'predis', $config);
});

// Set up Facade
Facade::setFacadeApplication($app);

// Create a new LeaderboardHandler instance
$leaderboard = new LeaderboardHandler($app);

// Example usage
echo "Laravel Leaderboard Standalone Example\n";
echo "=====================================\n\n";

// Insert some scores
$leaderboard->insertScore('user1', 100, ['featureId' => 'game1']);
$leaderboard->insertScore('user2', 150, ['featureId' => 'game1']);
$leaderboard->insertScore('user3', 75, ['featureId' => 'game1']);
$leaderboard->insertScore('user4', 200, ['featureId' => 'game1']);
$leaderboard->insertScore('user5', 180, ['featureId' => 'game1']);

echo "Scores inserted for 5 users\n\n";

// Get the leaderboard
$leaders = $leaderboard->getLeaderboard(['featureId' => 'game1']);
echo "Game1 Leaderboard:\n";
echo "----------------\n";
foreach ($leaders as $index => $userId) {
    $rank = $index + 1;
    $score = $leaderboard->getUserBestScore($userId, ['featureId' => 'game1']);
    echo "{$rank}. {$userId}: {$score['rawScore']} points\n";
}

echo "\n";

// Get a specific user's rank
$user = 'user3';
$rank = $leaderboard->getRank($user, ['featureId' => 'game1']);
echo "{$user}'s rank: " . ($rank + 1) . "\n\n";

// Get around me leaderboard
$aroundMe = $leaderboard->getAroundMeLeaderboard('user3', ['featureId' => 'game1', 'range' => 2]);
echo "Leaderboard around {$user}:\n";
echo "------------------------\n";
foreach ($aroundMe as $userId) {
    $rank = $leaderboard->getRank($userId, ['featureId' => 'game1']) + 1;
    $score = $leaderboard->getUserBestScore($userId, ['featureId' => 'game1']);
    echo "{$rank}. {$userId}: {$score['rawScore']} points\n";
}

echo "\n";

// Add periodic leaderboards
$leaderboard->addLeaderboards(['daily' => true, 'weekly' => true, 'monthly' => true]);
echo "Periodic leaderboards added (daily, weekly, monthly)\n";

// Insert scores for periodic leaderboards
$leaderboard->insertScore('user1', 120, ['featureId' => 'game1']);
$leaderboard->insertScore('user2', 170, ['featureId' => 'game1']);

echo "Additional scores inserted\n\n";

// Get daily leaderboard
$dailyLeaders = $leaderboard->getLeaderboard(['leaderboard' => 'daily', 'featureId' => 'game1']);
echo "Daily Leaderboard:\n";
echo "----------------\n";
foreach ($dailyLeaders as $index => $userId) {
    $rank = $index + 1;
    $score = $leaderboard->getUserBestScore($userId, ['leaderboard' => 'daily', 'featureId' => 'game1']);
    echo "{$rank}. {$userId}: {$score['rawScore']} points\n";
}

echo "\nNote: This example requires a Redis server running on localhost:6379\n";
echo "You can modify the Redis configuration in this script if needed.\n";