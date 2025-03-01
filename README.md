# Leaderboard inside your Laravel app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/canerdogan/laravel-leaderboard.svg?style=flat-square)](https://packagist.org/packages/canerdogan/laravel-leaderboard)
[![Total Downloads](https://img.shields.io/packagist/dt/canerdogan/laravel-leaderboard.svg?style=flat-square)](https://packagist.org/packages/canerdogan/laravel-leaderboard)

A Laravel leaderboard module that besides the all-time leaderboard supports also periodic leaderboards: daily, weekly, monthly options backed by [Redis](http://redis.io).

Here's a demo of how you can use it:


## Documentation

For detailed documentation on how to use the package, please see the [Usage](#usage) section below.

For information about how the package works internally, please see the [HOW_IT_WORKS.md](HOW_IT_WORKS.md) file.

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving the activity log? Feel free to [create an issue on GitHub](https://github.com/canerdogan/laravel-leaderboard/issues), we'll try to address it as soon as possible.



## Installation

You can install the package via composer:

``` bash
composer require canerdogan/laravel-leaderboard
```

The package will automatically register itself.


You can optionally publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [

    /*
     * If set to false, no activities will be saved to the database.
     */
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    /*
     * When the clean-command is executed, all recording activities older than
     * the number of days specified here will be deleted.
     */
    'delete_records_older_than_days' => 365,

    /*
     * If no log name is passed to the activity() helper
     * we use this default log name.
     */
    'default_log_name' => 'default',

    /*
     * You can specify an auth driver here that gets user models.
     * If this is null we'll use the default Laravel auth driver.
     */
    'default_auth_driver' => null,

    /*
     * If set to true, the subject returns soft deleted models.
     */
    'subject_returns_soft_deleted_models' => false,

    /*
     * This model will be used to log activity. The only requirement is that
     * it should be or extend the Spatie\Activitylog\Models\Activity model.
     */
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
    
    /*
     * This is the name of the table that will be created by the migration and
     * used by the Activity model shipped with this package.
     */
    'table_name' => 'activity_log',
];

```

## Usage

After installing the package, you can use the Leaderboard facade to interact with the leaderboard functionality.

### Configuration

First, you need to make sure Redis is properly configured in your Laravel application. The package uses the default Redis connection.

### Enabling Periodic Leaderboards

By default, only the all-time leaderboard is enabled. To enable periodic leaderboards (daily, weekly, monthly), you need to call the `addLeaderboards` method:

```php
use CanErdogan\Leaderboard\Facades\Leaderboard;

// Enable daily leaderboard
Leaderboard::addLeaderboards(['daily' => true]);

// Enable weekly leaderboard
Leaderboard::addLeaderboards(['weekly' => true]);

// Enable monthly leaderboard
Leaderboard::addLeaderboards(['monthly' => true]);

// Enable all periodic leaderboards
Leaderboard::addLeaderboards([
    'daily' => true,
    'weekly' => true,
    'monthly' => true
]);
```

### Inserting Scores

To insert a new score for a user:

```php
use CanErdogan\Leaderboard\Facades\Leaderboard;

// Basic score insertion
Leaderboard::insertScore('user123', 100);

// With feature ID
Leaderboard::insertScore('user123', 100, [
    'featureId' => 'game_level_1'
]);

// With additional score data
Leaderboard::insertScore('user123', 100, [
    'featureId' => 'game_level_1',
    'scoreData' => [
        'timeTaken' => 45,
        'livesLeft' => 2
    ]
]);
```

### Retrieving Leaderboards

To get a leaderboard:

```php
// Get all-time leaderboard
$leaderboard = Leaderboard::getLeaderboard();

// Get daily leaderboard
$dailyLeaderboard = Leaderboard::getLeaderboard([
    'leaderboard' => 'daily'
]);

// Get leaderboard for a specific feature
$featureLeaderboard = Leaderboard::getLeaderboard([
    'featureId' => 'game_level_1'
]);

// Get a specific range of the leaderboard
$topTen = Leaderboard::getLeaderboard([
    'fromRank' => 0,
    'toRank' => 9
]);
```

### Getting a User's Rank

To get a user's rank in a leaderboard:

```php
// Get rank in all-time leaderboard
$rank = Leaderboard::getRank('user123');

// Get rank in daily leaderboard
$dailyRank = Leaderboard::getRank('user123', [
    'leaderboard' => 'daily'
]);

// Get rank for a specific feature
$featureRank = Leaderboard::getRank('user123', [
    'featureId' => 'game_level_1'
]);
```

### Getting a User's Best Score

To get a user's best score:

```php
// Get best score from all-time leaderboard
$bestScore = Leaderboard::getUserBestScore('user123');

// Get best score from daily leaderboard
$dailyBestScore = Leaderboard::getUserBestScore('user123', [
    'leaderboard' => 'daily'
]);

// Get best score for a specific feature
$featureBestScore = Leaderboard::getUserBestScore('user123', [
    'featureId' => 'game_level_1'
]);

// Get best score with additional data
$bestScoreWithData = Leaderboard::getUserBestScore('user123', [
    'featureId' => 'game_level_1'
], [
    'rawscore' => true,
    'scoreData' => true,
    'date' => true
]);
```

### Getting Leaderboard Around a User

To get a portion of the leaderboard centered around a specific user:

```php
// Get 10 users around the specified user
$aroundMe = Leaderboard::getAroundMeLeaderboard('user123', [
    'range' => 5 // 5 users above and 5 users below
]);

// For a specific feature
$aroundMeFeature = Leaderboard::getAroundMeLeaderboard('user123', [
    'featureId' => 'game_level_1',
    'range' => 5
]);

// For a specific leaderboard period
$aroundMeDaily = Leaderboard::getAroundMeLeaderboard('user123', [
    'leaderboard' => 'daily',
    'range' => 5
]);
```

### Clearing Leaderboards

To clear all leaderboards:

```php
Leaderboard::flushAll();
```

To remove specific periodic leaderboards:

```php
Leaderboard::removeLeaderboards([
    'daily' => true,
    'weekly' => true,
    'monthly' => true
]);
```

For more detailed information about how the package works internally, please see the [HOW_IT_WORKS.md](HOW_IT_WORKS.md) file.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Can Erdogan](https://github.com/canerdogan)
- [All Contributors](../../contributors)

## Support us

Does your business depend on our contributions? Reach out and support us on [Buy Me a Coffee](https://buymeacoff.ee/canerdogan). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.