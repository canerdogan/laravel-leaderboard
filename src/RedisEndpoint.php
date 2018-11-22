<?php
/**
 * Created by PhpStorm.
 * User: can
 * Date: 2018-10-18
 * Time: 03:29
 */

namespace CanErdogan\Leaderboard;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisEndpoint
{

	const alltime = 'alltime';
	const daily   = 'daily';
	const weekly  = 'weekly';
	const monthly = 'monthly';

	const field_scoreId   = 'score_id';
	const field_userId    = 'user_id';
	const field_featureId = 'feature_id';
	const field_date      = 'date';
	const field_score     = 'score';
	const field_scoreData = 'score_data';

	const field_best_score = 'best_score';

	const key_score                         = 'score:';
	const key_userId                        = self::field_userId . ':';
	const key_featureAllTimeBestScoreId     = 'featureAllTimeBestScore:'; // key for sorted set for all time best scores
	const key_userFeatureAllTimeBestScoreId = 'userFeatureAllTimeBestScore:'; // key for hashes for user's best for a feature
	const key_featureDailyBestScoreId       = 'featureDailyBestScore:'; // key for sorted set for daily best scores
	const key_userFeatureDailyBestScoreId   = 'userFeatureDailyBestScore:';
	const key_featureWeeklyBestScoreId      = 'featureWeeklyBestScore:'; // key for sorted set for weekly best scores
	const key_userFeatureWeeklyBestScoreId  = 'userFeatureWeeklyBestScore:';
	const key_featureMonthlyBestScoreId     = 'featureMonthlyBestScore:'; // key for sorted set for monthly best scores
	const key_userFeatureMonthlyBestScoreId = 'userFeatureMonthlyBestScore:';

	protected $dailyBoard;

	protected $weeklyBoard;

	protected $monthlyBoard;


	public function __construct ()
	{
	}


	public function addPeriodicalLeaderboard ($period)
	{

		if($period === 'daily') {
			$this->dailyBoard = TRUE;
			Redis::connection()->set( 'dailyBoard', TRUE );
		} else if($period === 'weekly') {
			$this->weeklyBoard = TRUE;
			Redis::connection()->set( 'weeklyBoard', TRUE );
		} else if($period === 'monthly') {
			$this->monthlyBoard = TRUE;
			Redis::connection()->set( 'monthlyBoard', TRUE );
		}
	}


	/**
	 *  Flushes all
	 */
	public function flushAll ()
	{

		return Redis::connection()->flushall();
	}


	/**
	 * Clears the Daily leaderboard
	 *
	 * @param $period
	 */
	public function clearPeriodicalLeaderboard ($period)
	{

		$key_feature     = NULL;
		$key_userFeature = NULL;

		if($period == 'daily') {
			$key_feature     = self::key_featureDailyBestScoreId . "*";
			$key_userFeature = self::key_userFeatureDailyBestScoreId . "*";
		} else if($period == 'weekly') {
			$key_feature     = self::key_featureWeeklyBestScoreId . "*";
			$key_userFeature = self::key_userFeatureWeeklyBestScoreId . "*";
		} else if($period == 'monthly') {
			$key_feature     = self::key_featureMonthlyBestScoreId . "*";
			$key_userFeature = self::key_userFeatureMonthlyBestScoreId . "*";
		} else {
			// nothing to do
			return;
		}

		if( ! is_null( $key_feature )) {
			collect( Redis::connection()->keys( $key_feature ) )->each( function($keys) {

				Redis::connection()->del( $keys );
			} );
		}

		if( ! is_null( $key_userFeature )) {
			collect( Redis::connection()->keys( $key_userFeature ) )->each( function($keys) {

				Redis::connection()->del( $keys );
			} );
		}
	}


	/**
	 * Inserts a scoreboard
	 *
	 * @param $userid
	 * @param $featureid
	 * @param $date
	 * @param $rawScore
	 * @param $scoreData
	 *
	 * @return mixed
	 */
	public function insertScore ($userid, $featureid, $date, $rawScore, $scoreData)
	{

		$scoreId = Redis::connection()->incr( self::field_scoreId );

		return $this->addNewScore( $scoreId, $userid, $featureid, $date, $rawScore, $scoreData );
	}


	/**
	 * Adds new score to the score hashes and update user's best score
	 *
	 * @param $scoreid
	 * @param $userid
	 * @param $featureid
	 * @param $date
	 * @param $rawScore
	 * @param $scoreData
	 *
	 * @return mixed
	 */
	private function addNewScore ($scoreid, $userid, $featureid, $date, $rawScore, $scoreData)
	{

		Log::info( 'Inserting score: scoreId=' . $scoreid . ', userId=' . $userid . ', featureId=' . $featureid
		           . ', date=' . $date . ', rawScore=' . $rawScore . ', scoreData=' . print_r($scoreData, TRUE) );

		$resp = Redis::connection()->hmset( self::key_score . $scoreid,
		                                    self::field_userId, $userid,
		                                    self::field_featureId, $featureid,
		                                    self::field_date, $date,
		                                    self::field_score, $rawScore,
		                                    self::field_scoreData, json_encode( $scoreData )
		);

		$this->addUserPeriodicBestScore( $userid, $featureid, $scoreid, $rawScore );

		return $resp;
	}


	/**
	 * @param $userid
	 * @param $featureid
	 * @param $scoreid
	 * @param $rawScore
	 */
	private function addUserPeriodicBestScore ($userid, $featureid, $scoreid, $rawScore)
	{

		// add alltime bestscore
		$this->upsertUserBestScore( $userid, $featureid, $scoreid, $rawScore, self::key_userFeatureAllTimeBestScoreId,
		                            self::key_featureAllTimeBestScoreId );

		if($this->dailyBoard) {
			$this->upsertUserBestScore( $userid, $featureid, $scoreid, $rawScore, self::key_userFeatureDailyBestScoreId,
			                            self::key_featureDailyBestScoreId );
		}
		if($this->weeklyBoard) {
			$this->upsertUserBestScore( $userid, $featureid, $scoreid, $rawScore,
			                            self::key_userFeatureWeeklyBestScoreId, self::key_featureWeeklyBestScoreId );
		}
		if($this->monthlyBoard) {
			$this->upsertUserBestScore( $userid, $featureid, $scoreid, $rawScore,
			                            self::key_userFeatureMonthlyBestScoreId, self::key_featureMonthlyBestScoreId );
		}
	}


	/**
	 * Upserts user best score into the all time, daily, weekly, monthly scoreboards
	 *
	 * @param $userid
	 * @param $featureid
	 * @param $scoreid
	 * @param $rawScore
	 * @param $key_userFeatureBestScore
	 * @param $key_featureBestScore
	 */
	private function upsertUserBestScore (
		$userid, $featureid, $scoreid, $rawScore, $key_userFeatureBestScore, $key_featureBestScore
	) {

		$userFeatureBestScoreKey = $key_userFeatureBestScore . $userid . '_' . $featureid;

		$bestScoreId = Redis::connection()->hget( $userFeatureBestScoreKey, self::field_best_score );

		if($bestScoreId == NULL) {
			//hashed set of user and the score object id of his best score
			Redis::connection()->hset( $userFeatureBestScoreKey, self::field_best_score, $scoreid );

			//sorted set for each feature in order of rawSCore of the user
			Redis::connection()->zadd( $key_featureBestScore . $featureid, $rawScore, $userid );
		} else {
			// get score object to compare and replace best scoreId  if new score is higher
			$prevBestScore = Redis::connection()->hget( self::key_score . $bestScoreId, self::field_score );
			if($rawScore > $prevBestScore) {
				//hashed set of user and the score object id of his best score
				Redis::connection()->hset( $userFeatureBestScoreKey, self::field_best_score, $scoreid );

				//sorted set for each feature in order of rawSCore of the user
				Redis::connection()->zadd( $key_featureBestScore . $featureid, $rawScore, $userid );
			}
		}
	}


	/**
	 * [getUserBestScore description]
	 *
	 * @param $leaderboard
	 * @param $userid
	 * @param $featureid
	 * @param $returnOptions
	 *
	 * @return array
	 */
	public function getUserBestScore ($leaderboard, $userid, $featureid, $returnOptions)
	{

		$key = $this->getKeyUserFeatureBestScore( $leaderboard );

		return $this->getUserFeatureBestScore( $userid, $featureid, $key, $returnOptions );
	}


	/**
	 * @param $userid
	 * @param $featureid
	 * @param $key_userFeatureBestScore
	 * @param $returnOptions
	 *
	 * @return array
	 */
	private function getUserFeatureBestScore ($userid, $featureid, $key_userFeatureBestScore, $returnOptions)
	{

		$userFeatureBestScoreKey = $key_userFeatureBestScore . $userid . '_' . $featureid;

		$scoreId = Redis::connection()->hget( $userFeatureBestScoreKey, self::field_best_score );
		$score   = Redis::connection()->hgetall( self::key_score . $scoreId );

		if( ! is_array( $score )) {
			return $score;
		}

		return $this->returnFilteredScore( $score, $returnOptions );
	}


	/**
	 * @param $score
	 * @param $returnOptions
	 *
	 * @return array
	 */
	private function returnFilteredScore ($score, $returnOptions)
	{

		if($score === NULL) {
			return $score;
		}

		$returnOptions = $returnOptions ? $returnOptions : [];
		$rawScore      = array_key_exists( 'rawscore', $returnOptions ) ? $returnOptions['rawscore'] : TRUE;
		$scoreData     = array_key_exists( 'scoreData', $returnOptions ) ? $returnOptions['scoreData'] : FALSE;
		$date          = array_key_exists( 'date', $returnOptions ) ? $returnOptions['date'] : FALSE;

		$scoreObject = [];
		if($scoreData || $date) {
			if($scoreData) {
				$scoreObject['scoreData'] = $score['score_data'];
			}
			if($date) {
				$scoreObject['date'] = $score['date'];
			}
			if($rawScore) {
				$scoreObject['rawScore'] = $score['score'];
			}
		} else {
			$scoreObject = $score->score;
		}

		return $scoreObject;
	}


	/**
	 * @param $leaderboard
	 * @param $featureid
	 * @param $fromIndex
	 * @param $toIndex
	 *
	 * @return mixed
	 */
	public function getLeaderboard ($leaderboard, $featureid, $fromIndex, $toIndex)
	{

		$key = $this->getKeyUserFeatureBestScore( $leaderboard ) . $featureid;

		return Redis::connection()->zrevrange( $key, $fromIndex, $toIndex );
	}


	/**
	 * @param $userid
	 * @param $leaderboard
	 * @param $featureid
	 * @param $range
	 *
	 * @return mixed
	 */
	public function getAroundMeLeaderboard ($userid, $leaderboard, $featureid, $range)
	{

		$rank = $this->getRank( $leaderboard, $userid, $featureid );
		if( ! is_integer( $rank )) {
			return $rank;
		}
		$fromRank = $rank - $range;
		$toRank   = $rank + $range;

		return $this->getLeaderboard( $leaderboard, $featureid, $fromRank, $toRank );
	}


	/**
	 * Returns the rank of the user start from 0
	 *
	 * @param $leaderboard
	 * @param $userid
	 * @param $featureid
	 *
	 * @return mixed
	 */
	public function getRank ($leaderboard, $userid, $featureid)
	{

		$key = $this->getKeyUserFeatureBestScore( $leaderboard ) . $featureid;

		return Redis::connection()->zrevrank( $key, $userid );
	}


	/**
	 * @param $leaderboard
	 *
	 * @return string
	 */
	private function getKeyFeatureBestScore ($leaderboard)
	{

		if($leaderboard == 'daily') {
			return self::key_featureDailyBestScoreId;
		} else if($leaderboard == 'weekly') {
			return self::key_featureWeeklyBestScoreId;
		} else if($leaderboard == 'monthly') {
			return self::key_featureMonthlyBestScoreId;
		} else {
			return self::key_featureAllTimeBestScoreId;
		}
	}


	/**
	 * @param $leaderboard
	 *
	 * @return string
	 */
	private function getKeyUserFeatureBestScore ($leaderboard)
	{

		if($leaderboard == 'daily') {
			return self::key_userFeatureDailyBestScoreId;
		} else if($leaderboard == 'weekly') {
			return self::key_userFeatureWeeklyBestScoreId;
		} else if($leaderboard == 'monthly') {
			return self::key_userFeatureMonthlyBestScoreId;
		} else {
			return self::key_userFeatureAllTimeBestScoreId;
		}
	}
}