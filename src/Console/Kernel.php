<?php
/**
 * Created by PhpStorm.
 * User: can
 * Date: 2018-10-18
 * Time: 15:38
 */

namespace CanErdogan\Leaderboard\Console;

use CanErdogan\Leaderboard\Console\Commands\ClearLeaderboard;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

	protected function schedule (Schedule $schedule)
	{

		parent::schedule( $schedule );

		$schedule->command( ClearLeaderboard::class )->daily();
	}

}