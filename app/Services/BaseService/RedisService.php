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
use Illuminate\Support\Facades\Redis;

//use Illuminate\Contracts\Redis\Factory as Redis;

class RedisService extends Service
{
    public const AFTER = 'after';
    public const BEFORE = 'before';

    /**
     * Options
     */
    public const OPT_SERIALIZER = 1;
    public const OPT_PREFIX = 2;
    public const OPT_READ_TIMEOUT = 3;
    public const OPT_SCAN = 4;
    public const OPT_SLAVE_FAILOVER = 5;

    /**
     * Cluster options
     */
    public const FAILOVER_NONE = 0;
    public const FAILOVER_ERROR = 1;
    public const FAILOVER_DISTRIBUTE = 2;

    /**
     * SCAN options
     */
    public const SCAN_NORETRY = 0;
    public const SCAN_RETRY = 1;

    /**
     * Serializers
     */
    public const SERIALIZER_NONE = 0;
    public const SERIALIZER_PHP = 1;
    public const SERIALIZER_IGBINARY = 2;
    public const SERIALIZER_MSGPACK = 3;
    public const SERIALIZER_JSON = 4;

    /**
     * Multi
     */
    public const ATOMIC = 0;
    public const MULTI = 1;
    public const PIPELINE = 2;

    /**
     * Type
     */
    public const REDIS_NOT_FOUND = 0;
    public const REDIS_STRING = 1;
    public const REDIS_SET = 2;
    public const REDIS_LIST = 3;
    public const REDIS_ZSET = 4;
    public const REDIS_HASH = 5;

    //永久生效的ttl值
    public const TTL_NO_EXPIRE = -1;

    public const PINF = '+inf';    // 正无穷大
    public const NINF = '-inf';    // 负无穷大

    private $redis;

    /**
     * RedisService constructor.
     *
     * @param null $connection
     */
    public function __construct($connection = null)
    {
        $this->redis = Redis::connection($connection);
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

    /**
     * 读取bit值
     *
     * @param string $key
     * @param int    $offset 必须大于或等于 0 ，小于 2^32
     *
     * @return int 0 或 1
     */
    public function getBit($key, $offset)
    {
        return $this->redis->getBit($key, $offset);
    }

    /**
     * 设置bit值, 可用于大量用户的1:0的标记式存储
     * bit位数与内存关系:1千万=>2.9MB, 5千万=>8.7MB, 1亿=>16MB, 2亿=>28MB, 3亿=>40MB, 10亿=>124MB
     *
     * @param string $key
     * @param int    $offset  必须大于或等于 0 ，小于 2^32
     * @param int    $value,  0 或 1
     * @param int    $expire, 有效期，单位秒
     *
     * @return int 0 或 1, 该bit本次被设置之前的旧值
     */
    public function setBit($key, $offset, $value, $expire = 0)
    {
        $result = $this->redis->setBit($key, $offset, $value);

        if ($expire) {
            $this->redis->expire($key, $expire);
        }

        return $result;
    }

    /**
     * 获取key中，被设置为 1 的比特位的数量, 时间复杂度: O(N)
     *
     * @param string $key
     *
     * @return Long, 该bit本次被设置之前的旧值
     */
    public function getBitCount($key)
    {
        return $this->redis->bitCount($key);
    }

    /**
     * 对一个或多个保存二进制位的字符串 key 进行位元操作，并将结果保存到 destkey 上
     *
     * @param string $operation 取值有AND, OR, XOR, NOT
     * @param array  $keys
     * @param string $destKey
     * @param int    $expire,   有效期，单位秒
     *
     * @return int
     */
    public function bitOp($operation, $keys, $destKey, $expire = 3)
    {
        $params[] = $operation;
        $params[] = $destKey;
        $params = array_merge($params, $keys);
        $result = call_user_func_array([$this->redis, 'bitOp'], $params);
        if ($expire) {
            $this->redis->expire($destKey, $expire);
        }

        return $result;
    }

    /**
     * 获取key中所有值为1的bit位置, 如果存在1000及以上的bit有值为1的key，请不要对该key使用getAllBit.
     * 注意: 请慎用改接口, 该接口可能消耗极大的内存，所需内存值将约等于 (bit值为1的最高位的索引值)*8 (byte), 最大值大于2^32*8(byte)
     * 注意: 请慎用改接口, 该接口可能消耗极大的内存，所需内存值将约等于 (bit值为1的最高位的索引值)*8 (byte), 最大值大于2^32*8(byte)
     * 注意: 请慎用改接口, 该接口可能消耗极大的内存，所需内存值将约等于 (bit值为1的最高位的索引值)*8 (byte), 最大值大于2^32*8(byte)
     *
     * @param string $key
     *
     * @return array(offset) 返回值为1的bit位置，从0开始
     */
    public function getAllBit($key)
    {
        $result = $this->redis->get($key);
        $result = bin2hex($result);
        $result = str_split($result, 2);  //按单字节字符串截取
        $i = 0;
        $offset = [];
        foreach ($result as $hexByteStr) {
            if ($hexByteStr == '00') {
                $i++;
                continue;
            }
            $binStr = sprintf('%08s', base_convert($hexByteStr, 16, 2));
            for ($j = 0; $j < 8; $j++) {
                if ($binStr[$j] == '1') {
                    $offset[] = ($i * 8) + $j;
                }
            }
            $i++;
        }

        return $offset;
    }

    /**
     * HASH 操作
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     * @param $key
     * @param $value
     * @param int $expire
     *
     * @return mixed
     */
    public function hSet($h, $key, $value, $expire = 0)
    {
        $res = $this->redis->hSet($h, $key, $value);
        if ($expire) {
            $this->redis->expire($h, $expire);
        }

        return $res;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     * @param $data
     * @param int $expire
     *
     * @return mixed
     */
    public function hMset($h, $data, $expire = 0)
    {
        $res = $this->redis->hMset($h, $data);
        if ($expire) {
            $this->redis->expire($h, $expire);
        }

        return $res;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     * @param $key
     *
     * @return mixed
     */
    public function hGet($h, $key)
    {
        return $this->redis->hGet($h, $key);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $hash
     * @param $keys
     *
     * @return array
     */
    public function hMget($hash, $keys)
    {
        $result = $this->redis->hMget($hash, $keys);

        $members = [];
        foreach ($keys as $key) {
            if (false === $result[$key]) {
                continue;
            }
            $members[$key] = $result[$key];
        }

        return $members;
    }

    /**
     * 注意hIncrBy只接受整数, 如果可能是浮点数, 须用hIncrByFloat
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     * @param $key
     * @param int $step
     * @param int $expire
     *
     * @return mixed
     */
    public function hIncrBy($h, $key, $step = 1, $expire = 0)
    {
        $result = $this->redis->hIncrBy($h, $key, $step);
        if ($expire) {
            $this->redis->expire($h, $expire);
        }

        return $result;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     * @param $key
     * @param float $step
     * @param int   $expire
     *
     * @return mixed
     */
    public function hIncrByFloat($h, $key, $step = 1.0, $expire = 0)
    {
        $result = $this->redis->hIncrByFloat($h, $key, $step);
        if ($expire) {
            $this->redis->expire($h, $expire);
        }

        return $result;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     *
     * @return mixed
     */
    public function hGetAll($h)
    {
        return $this->redis->hGetAll($h);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     *
     * @return mixed
     */
    public function hKeys($h)
    {
        return $this->redis->hKeys($h);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     *
     * @return mixed
     */
    public function hVals($h)
    {
        return $this->redis->hVals($h);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     * @param $key
     *
     * @return mixed
     */
    public function hDel($h, $key)
    {
        return $this->redis->hDel($h, $key);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $h
     * @param $key
     *
     * @return mixed
     */
    public function hExists($h, $key)
    {
        return $this->redis->hExists($h, $key);
    }

    /** 分段扫描hash中的成员
     *
     * @param string $hash
     * @param mix    $cursor  第一次循环使用null, 后续循环传上一次hScan返回的cursor
     * @param int    $count   每次读取的成员数量, 当hash成员数不超过512时,此参数无效, hScan将返回全部成员
     * @param string $pattern 匹配模式
     *
     * @return array($cursor, $data) 其中data的格式为array($key => $value)
     */
    public function hScan($hash, $cursor, $count = 10, $pattern = '')
    {
        /*设置不返回空数据*/
        $this->redis->setOption(self::OPT_SCAN, self::SCAN_RETRY);

        $data = $this->redis->hScan($hash, $cursor, $pattern, $count);

        return [$cursor, $data];
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $hash
     *
     * @return mixed
     */
    public function hLen($hash)
    {
        return $this->redis->hLen($hash);
    }

    /******************List（列表）*********************/

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $value
     * @param $count
     *
     * @return mixed
     */
    public function listLRem($key, $value, $count)
    {
        return $this->redis->lRem($key, $value, $count);
    }

    /**
     * 返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定
     * 下标(index)参数 start 和 stop 都以 0 为底，也就是说，以 0 表示列表的第一个元素，以 1 表示列表的第二个元素，以此类推。
     * 也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $start
     * @param $stop
     *
     * @return mixed
     */
    public function listRange($key, $start, $stop)
    {
        return $this->redis->lRange($key, $start, $stop);
    }

    /**
     * 返回列表 key 的长度。
     * 如果 key 不存在，则 key 被解释为一个空列表，返回 0 .
     * 如果 key 不是列表类型，返回一个错误
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function listLength($key)
    {
        return $this->redis->lLen($key);
    }

    /**
     * 将value 插入到列表 key 的表头(最左边)
     *
     * @param string $value
     * @param bool   $createKey: 如果key不存在，是否创建
     *
     * @return int 返回执行成功后列表的长度
     */
    public function listLPush($key, $value, $createKey = true, $expire = 0)
    {
        $result = false;
        if ($createKey) {
            $result = $this->redis->lPush($key, $value);
        } else {
            $result = $this->redis->lPushx($key, $value);
        }
        if ($expire) {
            $this->redis->expire($key, $expire);
        }

        return $result;
    }

    /**
     * 将value 插入到列表 key 的表尾(最右边)
     *
     * @param string $value
     * @param bool   $createKey: 如果key不存在，是否创建
     * @param int    $expire
     *
     * @return int 返回执行成功后列表的长度
     */
    public function listRPush($key, $value, $createKey = true, $expire = 0)
    {
        $result = false;
        if ($createKey) {
            $result = $this->redis->rPush($key, $value);
        } else {
            $result = $this->redis->rPushX($key, $value);
        }
        if ($expire) {
            $this->redis->expire($key, $expire);
        }

        return $result;
    }

    /**
     * 批量将数组list 插入到列表 key 的表尾(最右边)
     *
     * @param array $memberList
     * @param bool  $createKey: 如果key不存在，是否创建
     *
     * @return int 返回执行成功后列表的长度
     */
    public function listRPushBatch($key, $memberList, $createKey = true, $expire = 0)
    {
        $params[] = $key;
        foreach ($memberList as $member) {
            $params[] = $member;
        }
        if ($createKey) {
            $result = call_user_func_array([$this->redis, 'rPush'], $params);
        } else {
            $result = call_user_func_array([$this->redis, 'rPush'], $params);
        }
        if ($expire) {
            $this->redis->expire($key, $expire);
        }

        return $result;
    }

    /**
     * 移除并返回列表 key 的头元素
     */
    public function listLPop($key)
    {
        return $this->redis->lPop($key);
    }

    /**
     * 移除并返回列表 key 的尾元素
     */
    public function listRPop($key)
    {
        return $this->redis->rPop($key);
    }

    /**
     * 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除。
     * 举个例子，执行命令 LTRIM list 0 2 ，表示只保留列表 list 的前三个元素，其余元素全部删除
     */
    public function listMultiPops($key, $start, $stop)
    {
        return $this->redis->lTrim($key, $start, $stop);
    }

    /**
     * 返回列表 key 中，下标为 index 的元素
     *
     * @param $key
     * @param $index
     *
     * @return string
     */
    public function listIndex($key, $index)
    {
        return $this->redis->lIndex($key, $index);
    }

    /**
     * 将值 value 插入到列表 key 当中，位于值 pivot 之前或之后。当 key | pivot 不存在于列表 key 时，不执行任何操作.
     *
     * @param $key
     * @param $pivot
     * @param $value
     * @param string $position : BEFORE | AFTER
     *
     * @return int
     */
    public function listInsert($key, $pivot, $value, $position = 'BEFORE')
    {
        return $this->redis->lInsert($key, $position, $pivot, $value);
    }

    /*******************set(集合）********************/

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $member
     *
     * @return mixed
     */
    public function sAdd($key, $member)
    {
        return $this->redis->sAdd($key, $member);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $member
     *
     * @return mixed
     */
    public function sRem($key, $member)
    {
        return $this->redis->srem($key, $member);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 分段扫描set中的成员
     *
     * @param string $s
     * @param mix    $cursor  第一次循环使用null, 后续循环传上一次sScan返回的cursor
     * @param int    $count   每次读取的成员数量, 当set成员数不超过512时,此参数无效, sScan将返回全部成员
     * @param string $pattern 匹配模式
     *
     * @return array($cursor, $data) 其中data的格式为array($key => $value)
     */
    public function sScan($s, $cursor, $count = 10, $pattern = '')
    {
        /*设置不返回空数据*/
        $this->redis->setOption(self::OPT_SCAN, self::SCAN_RETRY);

        $data = $this->redis->sScan($s, $cursor, $pattern, $count);

        return [$cursor, $data];
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $destination
     * @param $key1
     * @param $key2
     *
     * @return mixed
     */
    public function sInterStore($destination, $key1, $key2)
    {
        return $this->redis->sInterStore($destination, $key1, $key2);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $member
     *
     * @return mixed
     */
    public function sIsMember($key, $member)
    {
        return $this->redis->sismember($key, $member);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function sSize($key)
    {
        return $this->redis->sSize($key);
    }

    /******************hyperLog start**********************/

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $member
     *
     * @return mixed
     */
    public function pfAdd($key, $member)
    {
        if (!is_array($member)) {
            $member = [$member];
        }

        return $this->redis->pfadd($key, $member);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function pfCount($key)
    {
        return $this->redis->pfcount($key);
    }

    /**
     * 合并hyperLog
     *
     * @param string $distKey 目标key
     * @param array  $keys,   需要合并的key数组
     *
     * @return bool true or false
     */
    public function pfMerge($distKey, $keys)
    {
        return $this->redis->pfmerge($distKey, $keys);
    }

    /*******************互斥锁********************/

    /**
     * 获取互斥锁
     *
     * @param string $key
     * @param int    $timeout
     *
     * @return bool 是否获得锁
     */
    public function lock($key, $timeout = 10)
    {
        $key = self::getLockKey($key);
        $result = $this->redis->setnx($key, 1);
        if ($result == 1) {
            $this->redis->expire($key, $timeout);

            return true;
        }

        return false;
    }

    /**
     * 删除互斥锁
     *
     * @param string $key
     */
    public function unlock($key)
    {
        $key = self::getLockKey($key);
        $this->redis->del($key);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return string
     */
    private function getLockKey($key)
    {
        return 'lock:' . $key;
    }

    /******************通用key操作*********************/

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $args
     *
     * @return mixed
     */
    public function sort($key, $args)
    {
        return $this->redis->sort($key, $args);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $oldKey
     * @param $newKey
     *
     * @return mixed
     */
    public function rename($oldKey, $newKey)
    {
        return $this->redis->rename($oldKey, $newKey);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $timeout
     *
     * @return mixed
     */
    public function expire($key, $timeout)
    {
        return $this->redis->expire($key, $timeout);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     * @param $time
     *
     * @return mixed
     */
    public function expireAt($key, $time)
    {
        return $this->redis->expireAt($key, $time);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function delete($key)
    {
        return $this->redis->del($key);
    }

    /*******************其他********************/

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return mixed
     */
    public function flush()
    {
        return $this->redis->flushDB();
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     */
    public function ping()
    {
        $this->redis->ping();
    }

    /**
     * 选择数据库
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param int $db
     *
     * @return mixed
     */
    public function select($db = 0)
    {
        return $this->redis->select($db);
        ;
    }

    /**
     * 慎用, 时间复杂度为0(N), N为整个数据库的key数量
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return mixed
     */
    public function keys($key)
    {
        return $this->redis->keys($key);
    }

    /**
     * 比较危险哦，谨慎使用
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $cacheTag
     */
    public function deleteAll($cacheTag)
    {
        $list = $this->keys($cacheTag);

        foreach ($list as $key) {
            $this->delete($key);
        }
    }

    /**
     * 获取慢日志相关信息
     *
     * @param $cmd: This can be either GET, LEN, or RESET
     * @param int $count: If executing a SLOWLOG GET command, you can pass an optional length.
     *
     * @return mixed
     */
    public function slowLog($cmd, $count = 10)
    {
        return $this->redis->slowlog($cmd, $count);
    }

    /**
     * 获取redis运行统计信息
     *
     * @param string $section, 需要查询的片段, 可选, 取值如下
     *                         server: General information about the Redis server
     *                         clients: Client connections section
     *                         memory: Memory consumption related information
     *                         persistence: RDB and AOF related information
     *                         stats: General statistics
     *                         replication: Master/slave replication information
     *                         cpu: CPU consumption statistics
     *                         commandstats: Redis command statistics
     *                         cluster: Redis Cluster section
     *                         keyspace: Database related statistics
     *                         all: Return all sections
     *                         default: Return only the default set of sections
     *
     * @return array
     */
    public function info($section = 'default')
    {
        return $this->redis->info($section);
    }
}
