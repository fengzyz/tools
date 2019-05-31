<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/13
 * Time: 14:03
 */

use PHPUnit\Framework\TestCase;

class CacheRedisTest extends TestCase
{
    public function testPut()
    {
        $redis =  new \Fengzyz\Cache\CacheRedis();
    }
}