# Leaderboard inside your Laravel app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/canerdogan/laravel-leaderboard.svg?style=flat-square)](https://packagist.org/packages/canerdogan/laravel-leaderboard)
[![Total Downloads](https://img.shields.io/packagist/dt/canerdogan/laravel-leaderboard.svg?style=flat-square)](https://packagist.org/packages/canerdogan/laravel-leaderboard)

A Laravel leaderboard module that besides the all-time leaderboard supports also periodic leaderboards: daily, weekly, monthly options backed by [Redis](http://redis.io).

Here's a demo of how you can use it:


## Documentation


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
