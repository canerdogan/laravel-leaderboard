# Laravel Leaderboard - How It Works

This document provides a detailed explanation of how the Laravel Leaderboard package works, its architecture, and implementation details.

## Overview

Laravel Leaderboard is a package that provides leaderboard functionality for Laravel applications. It supports both all-time leaderboards and periodic leaderboards (daily, weekly, monthly) using Redis as the backend storage.

## Architecture

The package consists of several key components:

1. **LeaderboardHandler**: The main class that provides the API for interacting with leaderboards.
2. **RedisEndpoint**: Handles the Redis operations for storing and retrieving leaderboard data.
3. **Facade**: Provides a convenient way to access the leaderboard functionality.
4. **Service Provider**: Registers the package with Laravel.

## Data Structure

The package uses Redis to store leaderboard data with the following key structure:

- **Score**: `score:{scoreId}` - Stores individual score entries
- **User's Best Score**: `userFeatureAllTimeBestScore:{userId}_{featureId}` - Stores a user's best score for a specific feature
- **Feature Leaderboard**: `featureAllTimeBestScore:{featureId}` - Sorted set of users ranked by their scores for a specific feature

Similar keys exist for daily, weekly, and monthly leaderboards.

## How Scores Are Stored

When a new score is inserted:

1. A unique score ID is generated
2. The score is stored in Redis with user ID, feature ID, date, raw score, and any additional score data
3. The user's best score is updated if the new score is higher than their previous best
4. The leaderboard sorted sets are updated accordingly

## Periodic Leaderboards

The package supports periodic leaderboards that reset at specific intervals:

- **Daily**: Resets every day at midnight
- **Weekly**: Resets every week on Sunday
- **Monthly**: Resets on the first day of each month

These periodic leaderboards are managed through scheduled tasks that clear the relevant Redis keys at the specified intervals.

## Core Functionality

### Inserting Scores

```php
Leaderboard::insertScore($userId, $rawScore, [
    'featureId' => 'feature_name',
    'scoreData' => [
        'additionalData' => 'value'
    ]
]);
```

This inserts a new score for a user and updates the relevant leaderboards.

### Retrieving Leaderboards

```php
// Get the all-time leaderboard
Leaderboard::getLeaderboard([
    'leaderboard' => 'alltime',
    'featureId' => 'feature_name'
]);

// Get the daily leaderboard
Leaderboard::getLeaderboard([
    'leaderboard' => 'daily',
    'featureId' => 'feature_name'
]);
```

### Getting a User's Rank

```php
Leaderboard::getRank($userId, [
    'leaderboard' => 'alltime',
    'featureId' => 'feature_name'
]);
```

### Getting a User's Best Score

```php
Leaderboard::getUserBestScore($userId, [
    'leaderboard' => 'alltime',
    'featureId' => 'feature_name'
]);
```

### Getting Leaderboard Around a User

```php
Leaderboard::getAroundMeLeaderboard($userId, [
    'leaderboard' => 'alltime',
    'featureId' => 'feature_name',
    'range' => 5
]);
```

## Redis Implementation Details

The package uses Redis sorted sets (`ZADD`, `ZREVRANGE`, `ZREVRANK`) for efficient leaderboard operations:

- `ZADD` is used to add or update scores in the leaderboard
- `ZREVRANGE` is used to retrieve a range of users from the leaderboard
- `ZREVRANK` is used to get a user's rank in the leaderboard

Redis hashes are used to store score details and user's best scores.

## Performance Considerations

- Redis operations are very fast, making the leaderboard operations efficient
- The package is designed to minimize Redis operations by only updating a user's best score when necessary
- Periodic leaderboards are cleared using scheduled tasks, which can be resource-intensive for large leaderboards

## Extending the Package

The package can be extended in several ways:

1. Adding new types of periodic leaderboards
2. Implementing custom score calculation logic
3. Adding additional metadata to scores
4. Implementing custom leaderboard filtering

## Conclusion

Laravel Leaderboard provides a robust and efficient way to implement leaderboard functionality in Laravel applications. By leveraging Redis's sorted sets, it offers high-performance leaderboard operations with minimal overhead.