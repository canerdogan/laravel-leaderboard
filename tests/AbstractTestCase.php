<?php
/**
 * Created by PhpStorm.
 * User: can
 * Date: 2018-10-18
 * Time: 16:49
 */

namespace Tests;

use CanErdogan\Leaderboard\RedisEndpoint;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
	protected $redisClient;

	protected function setUp ()
	{
		$this->redisClient = new RedisEndpoint();
	}

}