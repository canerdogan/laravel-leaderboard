# Laravel Leaderboard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/canerdogan/laravel-leaderboard.svg?style=flat-square)](https://packagist.org/packages/canerdogan/laravel-leaderboard)
[![Total Downloads](https://img.shields.io/packagist/dt/canerdogan/laravel-leaderboard.svg?style=flat-square)](https://packagist.org/packages/canerdogan/laravel-leaderboard)

Laravel leaderboard module that supports both all-time leaderboards and periodic leaderboards (daily, weekly, monthly) backed by Redis.

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x
- Redis server

## Installation

You can install the package via composer:

```bash
composer require canerdogan/laravel-leaderboard
```

The package will automatically register its service provider.

## Configuration

This package requires Redis to be configured in your Laravel application. Make sure you have the Redis configuration set up in your `config/database.php` file:

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'predis'),
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
],
```

## Usage

### Basic Usage

```php
use CanErdogan\Leaderboard\Facades\Leaderboard;

// Insert a score for a user
Leaderboard::insertScore('user123', 100, [
    'featureId' => 'game1',
    'scoreData' => [
        'level' => 5,
        'time' => 120,
        'bonus' => 20
    ]
]);

// Get the all-time leaderboard
$leaders = Leaderboard::getLeaderboard([
    'featureId' => 'game1',
    'fromRank' => 0,
    'toRank' => 9 // Top 10 players
]);

// Get a user's best score
$score = Leaderboard::getUserBestScore('user123', [
    'featureId' => 'game1'
], [
    'rawscore' => true,
    'scoreData' => true,
    'date' => true
]);

// Get a user's rank
$rank = Leaderboard::getRank('user123', [
    'featureId' => 'game1'
]);

// Get leaderboard around a specific user
$aroundMe = Leaderboard::getAroundMeLeaderboard('user123', [
    'featureId' => 'game1',
    'range' => 5 // 5 players above and below
]);
```

### Periodic Leaderboards

The package supports daily, weekly, and monthly leaderboards. You need to enable them first:

```php
// Enable periodic leaderboards
Leaderboard::addLeaderboards([
    'daily' => true,
    'weekly' => true,
    'monthly' => true
]);

// Get the daily leaderboard
$dailyLeaders = Leaderboard::getLeaderboard([
    'leaderboard' => 'daily',
    'featureId' => 'game1',
    'fromRank' => 0,
    'toRank' => 9
]);

// Get the weekly leaderboard
$weeklyLeaders = Leaderboard::getLeaderboard([
    'leaderboard' => 'weekly',
    'featureId' => 'game1',
    'fromRank' => 0,
    'toRank' => 9
]);

// Get the monthly leaderboard
$monthlyLeaders = Leaderboard::getLeaderboard([
    'leaderboard' => 'monthly',
    'featureId' => 'game1',
    'fromRank' => 0,
    'toRank' => 9
]);
```

### Clearing Leaderboards

You can clear the leaderboards using the provided command:

```bash
php artisan leaderboard:clear
```

Or programmatically:

```php
use CanErdogan\Leaderboard\RedisEndpoint;

$redisEndpoint = new RedisEndpoint();
$redisEndpoint->clearPeriodicalLeaderboard('daily');
$redisEndpoint->clearPeriodicalLeaderboard('weekly');
$redisEndpoint->clearPeriodicalLeaderboard('monthly');
```

### Standalone Usage

You can also use the package without a full Laravel application. See the example in `examples/standalone.php`.

### Laravel Controller Example

For a complete example of how to use the package in a Laravel controller, see the example in `examples/laravel-usage.php`.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.