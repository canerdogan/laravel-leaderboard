<?php namespace CanErdogan\Leaderboard\Facades;

use CanErdogan\Leaderboard\LeaderboardHandler;
use Illuminate\Support\Facades\Facade;

class Leaderboard extends Facade
{

	/**
	 * Get a schema builder instance for the default connection.
	 *
	 * @return \Rollbar\Laravel\RollbarLogHandler
	 */
	protected static function getFacadeAccessor ()
	{

		return LeaderboardHandler::class;
	}
}
