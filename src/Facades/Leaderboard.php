<?php namespace CanErdogan\Leaderboard\Facades;

use CanErdogan\Leaderboard\LeaderboardHandler;
use Illuminate\Support\Facades\Facade;

class Leaderboard extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return LeaderboardHandler::class;
    }
}