<?php namespace CanErdogan\Leaderboard;

use Illuminate\Contracts\Foundation\Application;
use Carbon\Carbon;

class LeaderboardHandler
{

	const alltime       = 'alltime';
	const daily         = 'daily';
	const weekly        = 'weekly';
	const monthly       = 'monthly';
	const test_secondly = 'test_secondly';

	const default_featureId = 'NONE';

	private $redisClient;

	/**
	 * The Laravel application.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;


	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->redisClient = new RedisEndpoint();
	}


	private function scheduleLbClear(string $cronPattern, string $period)
	{
		$res = $this->redisClient->clearPeriodicalLeaderboard($period);
	}


	/**
	 * @param       $userId
	 * @param       $rawScore
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function insertScore($userId, $rawScore, array $options = [])
	{
		$featureId = array_key_exists('featureId', $options) ? $options['featureId'] : self::default_featureId;
		$date      = array_key_exists('date', $options) ? $options['date'] : Carbon::now()->toIso8601String();
		$scoreData = array_key_exists('scoreData', $options) ? $options['scoreData'] : [];

		return $this->redisClient->insertScore($userId, $featureId, $date, $rawScore, $scoreData);
	}


	/**
	 * @param       $userId
	 * @param array $getOptions
	 * @param array $returnOptions
	 *
	 * @return array
	 */
	public function getUserBestScore($userId, array $getOptions = [], array $returnOptions = []): array
	{
		$leaderboard = array_key_exists('leaderboard', $getOptions) ? $getOptions['leaderboard'] : self::alltime;
		$featureId   = array_key_exists('featureId', $getOptions) ? $getOptions['featureId'] : self::default_featureId;

		return $this->redisClient->getUserBestScore($leaderboard, $userId, $featureId, $returnOptions);
	}


	/**
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function getLeaderboard(array $options = [])
	{
		$leaderboard = array_key_exists('leaderboard', $options) ? $options['leaderboard'] : self::alltime;
		$featureId   = array_key_exists('featureId', $options) ? $options['featureId'] : self::default_featureId;
		$fromRank    = array_key_exists('fromRank', $options) ? $options['fromRank'] : 0;
		$toRank      = array_key_exists('toRank', $options) ? $options['toRank'] : -1; // entire leaderboard

		return $this->redisClient->getLeaderboard($leaderboard, $featureId, $fromRank, $toRank);
	}


	/**
	 * @param       $userId
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function getAroundMeLeaderboard($userId, array $options = [])
	{
		$leaderboard = array_key_exists('leaderboard', $options) ? $options['leaderboard'] : self::alltime;
		$featureId   = array_key_exists('featureId', $options) ? $options['featureId'] : self::default_featureId;
		$range       = array_key_exists('range', $options) ? $options['range'] : 10;

		return $this->redisClient->getAroundMeLeaderboard($leaderboard, $featureId, $range);
	}


	/**
	 * @param       $userId
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function getRank($userId, array $options = [])
	{
		$leaderboard = array_key_exists('leaderboard', $options) ? $options['leaderboard'] : self::alltime;
		$featureId   = array_key_exists('featureId', $options) ? $options['featureId'] : self::default_featureId;

		return $this->redisClient->getRank($leaderboard, $userId, $featureId);
	}


	/**
	 * @return mixed
	 */
	public function flushAll()
	{
		return $this->redisClient->flushAll();
	}


	/**
	 * Add leaderboards with specified options
	 * 
	 * @param array $options
	 * @return void
	 */
	public function addLeaderboards(array $options = []): void
	{
		$daily   = array_key_exists('daily', $options) ? $options['daily'] : FALSE;
		$weekly  = array_key_exists('weekly', $options) ? $options['weekly'] : FALSE;
		$monthly = array_key_exists('monthly', $options) ? $options['monthly'] : FALSE;
		$test    = array_key_exists('test', $options) ? $options['test'] : FALSE;

		if($test) {
			$this->secondlyTask = $this->scheduleLbClear('* * * * * *', 'daily');
			$this->redisClient->addPeriodicalLeaderboard('daily');
		}
		if($daily) {
			$this->dailyTask = $this->scheduleLbClear('0 0 * * *', 'daily');
			$this->redisClient->addPeriodicalLeaderboard('daily');
		}

		if($weekly) {
			$this->weeklyTask = $this->scheduleLbClear('0 0 * * 0', 'weekly');
			$this->redisClient->addPeriodicalLeaderboard('weekly');
		}

		if($monthly) {
			$this->monthlyTask = $this->scheduleLbClear('0 0 0 1 *', 'monthly');
			$this->redisClient->addPeriodicalLeaderboard('monthly');
		}
	}


	/**
	 * Remove leaderboards with specified options
	 * 
	 * @param array $options
	 * @return void
	 */
	public function removeLeaderboards(array $options = []): void
	{
		$daily   = array_key_exists('daily', $options) ? $options['daily'] : FALSE;
		$weekly  = array_key_exists('weekly', $options) ? $options['weekly'] : FALSE;
		$monthly = array_key_exists('monthly', $options) ? $options['monthly'] : FALSE;
		$test    = array_key_exists('test', $options) ? $options['test'] : FALSE;

		if($test && isset($this->secondlyTask)) {
			$this->secondlyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard('daily');
		}

		if($daily && isset($this->dailyTask)) {
			$this->dailyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard('daily');
		}

		if($weekly && isset($this->weeklyTask)) {
			$this->weeklyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard('weekly');
		}

		if($monthly && isset($this->monthlyTask)) {
			$this->monthlyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard('monthly');
		}
	}
}