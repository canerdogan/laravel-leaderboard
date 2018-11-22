<?php namespace CanErdogan\Leaderboard;

use Illuminate\Foundation\Application;
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
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;


	public function __construct (Application $app)
	{

		$this->redisClient = new RedisEndpoint();
	}


	private function scheduleLbClear ($cronPattern, $period)
	{
		+
		$res = $this->redisClient->clearPeriodicalLeaderboard($period);

	}


	/**
	 * @param       $userId
	 * @param       $rawScore
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function insertScore ($userId, $rawScore, $options = [])
	{

		$featureId = array_key_exists( 'featureId', $options ) ? $options['featureId'] : self::default_featureId;
		$date      = array_key_exists( 'date', $options ) ? $options['date'] : Carbon::now()->toIso8601String();
		$scoreData = array_key_exists( 'scoreData', $options ) ? $options['scoreData'] : [];

		return $this->redisClient->insertScore( $userId, $featureId, $date, $rawScore, $scoreData );
	}


	/**
	 * @param       $userId
	 * @param array $getOptions
	 * @param array $returnOptions
	 *
	 * @return array
	 */
	public function getUserBestScore ($userId, $getOptions = [], $returnOptions = [])
	{

		$leaderboard = array_key_exists( 'leaderboard', $getOptions ) ? $getOptions['leaderboard'] : self::alltime;
		$featureId   = array_key_exists( 'featureId',
		                                 $getOptions ) ? $getOptions['featureId'] : self::default_featureId;

		return $this->redisClient->getUserBestScore( $leaderboard, $userId, $featureId, $returnOptions );
	}


	/**
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function getLeaderboard ($options = [])
	{

		$leaderboard = array_key_exists( 'leaderboard', $options ) ? $options['leaderboard'] : self::alltime;
		$featureId   = array_key_exists( 'featureId', $options ) ? $options['featureId'] : self::default_featureId;
		$fromRank    = array_key_exists( 'fromRank', $options ) ? $options['fromRank'] : 0;
		$toRank      = array_key_exists( 'toRank', $options ) ? $options['toRank'] : - 1; // entire leaderboard

		return $this->redisClient->getLeaderboard( $leaderboard, $featureId, $fromRank, $toRank );
	}


	/**
	 * @param       $userId
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function getAroundMeLeaderboard ($userId, $options = [])
	{

		$leaderboard = array_key_exists( 'leaderboard', $options ) ? $options['leaderboard'] : self::alltime;
		$featureId   = array_key_exists( 'featureId', $options ) ? $options['featureId'] : self::default_featureId;
		$range       = array_key_exists( 'range', $options ) ? $options['range'] : 10;

		return $this->redisClient->getAroundMeLeaderboard( $leaderboard, $featureId, $range );
	}


	/**
	 * @param       $userId
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function getRank ($userId, $options = [])
	{

		$leaderboard = array_key_exists( 'leaderboard', $options ) ? $options['leaderboard'] : self::alltime;
		$featureId   = array_key_exists( 'featureId', $options ) ? $options['featureId'] : self::default_featureId;

		return $this->redisClient->getRank( $leaderboard, $userId, $featureId );
	}


	/**
	 * @return mixed
	 */
	public function flushAll ()
	{

		return $this->redisClient->flushAll();
	}


	// TODO: Bu fonksiyon eksik Bunuun için zamanlanmış task çalıştırmak gerekiyor.
	public function addLeaderboards ($options = [])
	{

		$daily   = array_key_exists( 'daily', $options ) ? $options['daily'] : FALSE;
		$weekly  = array_key_exists( 'weekly', $options ) ? $options['weekly'] : FALSE;
		$monthly = array_key_exists( 'monthly', $options ) ? $options['monthly'] : FALSE;
		$test    = array_key_exists( 'test', $options ) ? $options['test'] : FALSE;

		if($test) {
			$this->secondlyTask = $this->scheduleLbClear( '* * * * * *' );
			$this->redisClient->addPeriodicalLeaderboard( 'daily' );
		}
		if($daily) {
			$this->dailyTask = $this->scheduleLbClear( '0 0 * * *' );
			$this->redisClient->addPeriodicalLeaderboard( 'daily' );
		}

		if($weekly) {
			$this->weeklyTask = $this->scheduleLbClear( '0 0 * * 0' );
			$this->redisClient->addPeriodicalLeaderboard( 'weekly' );
		}

		if($monthly) {
			$this->monthlyTask = $this->scheduleLbClear( '0 0 0 1 *' );
			$this->redisClient->addPeriodicalLeaderboard( 'montyly' );
		}
	}


	// TODO: dailyTask vs. veritabanına kaydedilebilir oradaki bilgilere göre job çalıştırılır/silinir.
	public function removeLeaderboards ($options = [])
	{

		$daily   = array_key_exists( 'daily', $options ) ? $options['daily'] : FALSE;
		$weekly  = array_key_exists( 'weekly', $options ) ? $options['weekly'] : FALSE;
		$monthly = array_key_exists( 'monthly', $options ) ? $options['monthly'] : FALSE;
		$test    = array_key_exists( 'test', $options ) ? $options['test'] : FALSE;

		if($test && $this->secondlyTask != NULL) {
			//			this.logger.debug('Destroy test_secondly task');
			$this->secondlyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard( 'daily' );
		}

		if($daily && $this->dailyTask != NULL) {
			$this->dailyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard( 'daily' );
		}

		if($weekly && $this->weeklyTask != NULL) {
			$this->weeklyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard( 'weekly' );
		}

		if($monthly && $this->monthlyTask != NULL) {
			$this->monthlyTask->destroy();
			$this->redisClient->clearPeriodicalLeaderboard( 'monthly' );
		}
	}
}