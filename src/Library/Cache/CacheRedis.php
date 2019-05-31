<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/13
 * Time: 10:51
 */

namespace Fengzyz\Cache;

use Illuminate\Support\Facades\Cache;

/**
 * Class CacheRedis
 * @package Fengzyz\Cache
 *
 */
class CacheRedis
{
    private static $cache;

    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    protected static function getDrive()
    {
        return Cache::store('redis');
    }

    /**
     *  存储缓存项到缓存
     * @param $key
     * @param $name
     * @param int $expiryTime
     */
    public static function put($key, $name, $expiryTime = 86400)
    {
        self::getDrive()->put($key, $name, $expiryTime);
    }

    /**
     * add方法只会在缓存项不存在的情况下添加缓存项到缓存，如果缓存项被添加到缓存返回true，否则，返回false：
     * @param $key
     * @param $name
     * @param int $expiryTime
     * @return mixed
     */
    public static function add($key, $name, $expiryTime = 86400)
    {
        return self::getDrive()->add($key, $name, $expiryTime);
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function get($name)
    {
        return self::getDrive()->get($name);
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function pull($key)
    {
        return self::getDrive()->pull($key);
    }

    /**
     *  从缓存中移除数据
     * @param $key
     * @return mixed
     */
    public static function forget($key)
    {
        return self::getDrive()->forget($key);
    }

    /**
     * 设置缓存
     * @param $key
     * @param $func
     * @param int $time
     * @return array
     */
    public static function cacheResult($key, $func, $expiryTime = 86400)
    {
        $redisObj = self::getDrive();
        $data = self::isOpen() ? $redisObj->get($key) : '';
        if (empty($data)) {
            $data = [];
            if (is_callable($func)) {
                $data = $func();
            }
            $expiryTime = self::isOpen() ? $expiryTime : 0;
            $redisObj->add($key, $data, $expiryTime);
        }
        if (empty($expiryTime)) {
            $redisObj->forget($key);
        }
        return $data;
    }

    /**
     *  是否打开缓存
     */
    protected static function isOpen()
    {
        return env('CACHE_OPEN', true);
    }
}