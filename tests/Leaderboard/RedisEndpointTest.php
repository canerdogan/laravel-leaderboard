<?php
/**
 * Created by PhpStorm.
 * User: can
 * Date: 2018-10-18
 * Time: 16:53
 */

namespace Tests\Leaderboard;

use Tests\AbstractTestCase;

class RedisEndpointTest extends AbstractTestCase
{

	protected function setUp ()
	{

		parent::setUp();
		$this->redisClient->addPeriodicalLeaderboard( 'daily' );

		//		$date = Carbon::now()->toIso8601String();
		$this->redisClient->insertScore( '1', 100, [
			'featureId' => 'quiz 1',
			'scoreData' => [
				'timeTaken' => '1000',
			]
		] );
		//		$this->redisClient->insertScore( '1', 'quiz 1', $date, 100, ['timeTaken' => '1000'] );
		//		$this->redisClient->insertScore( '1', 'quiz 1', $date, 200, ['timeTaken' => '2000'] );
		//		$this->redisClient->insertScore( '2', 'quiz 1', $date, 200, ['timeTaken' => '3000'] );
		//		$this->redisClient->insertScore( '3', 'quiz 1', $date, 500, ['timeTaken' => '4000'] );
		//		$this->redisClient->insertScore( '1', 'quiz 2', $date, 100, ['timeTaken' => '100'] );
	}


	protected function tearDown ()
	{

		parent::tearDown();

		$this->redisClient->flushAll();
	}


	public function testGetAllTimeRank ()
	{

		$rank = $this->redisClient->getRank( 'alltime', '1', 'quiz 1' );

		$this->assertEquals( 2, $rank );
	}

}