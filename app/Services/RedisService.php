<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 16:41
 */
namespace App\Services;

use Illuminate\Contracts\Redis\Factory as Redis;

class RedisService extends Service
{
    private $redis;

    /**
     * RedisService constructor.
     *
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * redis自增
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function redisIncr($key)
    {
        $incr = $this->redis->incr($key);

        return $incr;
    }

    /**
     * Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $key
     *
     * @return bool
     */
    public function isRedisExists($key)
    {
        $bool = $this->redis->exists($key);

        return $bool;
    }

    /**
     * Author huaixiu.zhen
     * http://litblc.com
     *
     * @param  $key
     * @param  $val
     * @param string $ex
     * @param int    $ttl
     *
     * @return string ok
     */
    public function setRedis($key, $val, $ex = 'EX', $ttl = 600)
    {
        $bool = $this->redis->set($key, $val, $ex, $ttl);

        return $bool;
    }

    /**
     * Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $key
     *
     * @return string or null
     */
    public function getRedis($key)
    {
        $res = $this->redis->get($key);

        return $res;
    }

    /**
     * 剩余过期时间
     *
     * Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function getRedisTtl($key)
    {
        $ttl = $this->redis->ttl($key);

        return $ttl;
    }
}
