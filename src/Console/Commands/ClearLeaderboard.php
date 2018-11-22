<?php
/**
 * Created by PhpStorm.
 * User: can
 * Date: 2018-10-18
 * Time: 15:47
 */

namespace CanErdogan\Leaderboard\Console\Commands;

use Illuminate\Console\Command;

class ClearLeaderboard extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'leaderboard:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clearing leaderboards';


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct ()
	{

		parent::__construct();
	}


	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle ()
	{

		$users = User::all();

		foreach($users as $user) {
			$user->avatar = 'public/avatars/avatar-' . rand( 1, 9 ) . '.png';
			$user->save();
		}
	}
}