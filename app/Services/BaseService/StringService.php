<?php
/**
 * 字符串相关生成与处理
 * 雪花算法
 *
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2020/5/13
 * Time: 14:13
 */

namespace App\Services\BaseService;

use App\Services\Service;

class StringService extends Service
{
    public const debug = 1;

    /**
     * 毫秒内自增数点的位数
     */
    public const sequenceBits = 10;

    private static $sequence = 0;
    private static $sequenceMask = 1023;

    /**
     * 机器id
     */
    private static $workerId = 1;

    /**
     * 机器ID偏左移10位
     */
    private static $workerIdShift = self::sequenceBits;

    /**
     * 开始时间 固定、小于当前时间的毫秒
     */
    private static $twepoch = 1361775855078;

    /**
     * 时间毫秒左移14位
     */
    private static $timestampLeftShift = 14;

    private static $lastTimestamp = -1;

    /**
     * 分布式自增ID
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @throws \Exception
     *
     * @return int
     */
    public static function nextId()
    {
        $timestamp = self::timeGen();
        if (self::$lastTimestamp == $timestamp) {
            self::$sequence = (self::$sequence + 1) & self::$sequenceMask;
            if (self::$sequence == 0) {
                $timestamp = self::tilNextMillis(self::$lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }
        if ($timestamp < self::$lastTimestamp) {
            throw new \Exception('Clock moved backwards. Refusing to generate id for ' . (self::$lastTimestamp - $timestamp) . ' milliseconds');
        }
        self::$lastTimestamp = $timestamp;
        $nextId = ((sprintf('%.0f', $timestamp) - sprintf('%.0f', self::$twepoch)) << self::$timestampLeftShift)
            | (self::$workerId << self::$workerIdShift) | self::$sequence;

        return (int) $nextId;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return string
     */
    private static function timeGen()
    {
        $time = explode(' ', microtime());
        $time2 = substr($time[0], 2, 3);

        return $time[1] . $time2;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $lastTimestamp
     *
     * @return string
     */
    private static function tilNextMillis($lastTimestamp)
    {
        $timestamp = self::timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = self::timeGen();
        }

        return $timestamp;
    }
}
