<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 16:41
 */
namespace App\Services\BaseService;

use App\Services\Service;
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

    /**
     * 有序集合
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $key
     * @param $score
     * @param $val
     *
     * @return mixed
     */
    public function zadd($key, $score, $val)
    {
        $bool = $this->redis->zadd($key, $score, $val);

        return $bool;
    }

    /**
     * 删除集合中的某个值
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $key
     * @param $val
     *
     * @return mixed
     */
    public function zrem($key, $val)
    {
        $bool = $this->redis->zrem($key, $val);

        return $bool;
    }

    /**
     * 查询集合中是否存在某个值
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $key
     * @param $val
     *
     * @return mixed
     */
    public function zscore($key, $val)
    {
        $bool = $this->redis->zscore($key, $val);

        return $bool;
    }

    /**
     * 顺序获取集合中的值
     *
     * Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $key
     * @param $start
     * @param $end
     * @param $score
     *
     * @return string or null
     */
    public function zrange($key, $start, $end, $score = false)
    {
        if ($score) {
            $res = $this->redis->zrange($key, $start, $end, 'withscores');
        } else {
            $res = $this->redis->zrange($key, $start, $end);
        }

        return $res;
    }

    /**
     * 倒序获取集合中的值
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $key
     * @param $start
     * @param $end
     * @param $score
     *
     * @return mixed
     */
    public function zrevrange($key, $start, $end, $score = false)
    {
        if ($score) {
            $res = $this->redis->zrevrange($key, $start, $end, 'withscores');
        } else {
            $res = $this->redis->zrevrange($key, $start, $end);
        }

        return $res;
    }
}
