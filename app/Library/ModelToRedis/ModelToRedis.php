<?php
/**
 * 缓存模型 从数据库中获取数据后 自动加人redis中进行缓存 支持行级、表级、分区级
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2019/11/15
 * Time: 14:46
 */

namespace App\Library\ModelToRedis;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseService\RedisService;

abstract class ModelToRedis extends Model
{
    use ModelDal;

    public const TAG_FIELDS_VERSION = 'fieldsVer';

    public const TAG_DATA = 'data';

    public const TAG_VERSION_TABLE = 'VerT';
    public const TAG_VERSION_SHARD = 'VerS';
    public const TAG_VERSION_ALL_SHARDS = 'VerAS';
    public const TAG_VERSION_LINE = 'VerL';

    public const TAG_LINE_GET_BY_ID = 'id';
    public const TAG_LINE_GET_BY_UK = 'uk';

    //表级缓存: 只要表中数据有任意变化，该表的所有表级缓存数据都将失效
    public const CACHE_LEVEL_TABLE = 1;
    //分区级缓存: 意味着修改分区A中的数据，不影响分区B中的数据缓存;
    //这里的分区只是根据某个键进行逻辑上的划分，如用户账单表，最佳的缓存效果是用户A的账单变化不应当影响用户B的账单缓存数据，如此可以将用户ID作为缓存的分区键
    public const CACHE_LEVEL_SHARD = 2;
    //行级缓存: 最精确的缓存管理模式，只对数据库的主键和唯一键查询结果进行行级缓存
    public const CACHE_LEVEL_LINE = 3;

    public const MAX_VERSION = 999999999;
    public const MIN_VERSION = 1;
    public const INVALID_VERSION = -1;

    private $mLineCacheExpireTime = 3600;
    private $mLineCacheVerExpireTime = 86400;
    private $mTableCacheExpireTime = 3600;
    private $mTableCacheVerExpireTime = 86400;
    private $mShardCacheExpireTime = 3600;
    private $mShardCacheVerExpireTime = 3600;
    private $mAllShardsCacheVerExpireTime = 86400;

    private $mEnableTableCache = false;

    private $mRedisId;
    private $mShardKeys = [];
    private $mTransactionObserver = [];

    //部分后台任务会从数据库查询大量数据, 而这些数据有可能因量太大或者没有缓存价值,
    //需要临时禁止查询数据入缓存
    private $mDisableCache = false;

    private static $sAllowOldCache = true;

    // 设置使用的redis实例 配合$sSqlRedisList使用 具体参考config/database.php
    private $redisConnection = 0;

    // laravel 配置的redis缓存实例名称
    private static $sSqlRedisList = [
        'default',
        'cache',
    ];

    private $redisUtil;

    /**
     * 是否使用表级缓存，返回 true 或 false
     */
    abstract protected function onEnableTableCache();

    /**
     * 用于分区缓存, 如不需要使用分区缓存, 返回空数组, 否则返回用户分区的数据库列名的数组
     *
     * @return $shardKeys array, 用于指定哪些列用来分区, 如array('userId')
     */
    abstract protected function onShardKeys();

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->mEnableTableCache = $this->onEnableTableCache();
        $this->setShardKeys();
        $this->mRedisId = $this->onGetRedisId();
    }

    /**
     * 当增/删/改字段时，子类可通过该方法升级版本号，使得旧的sql缓存立即失效
     */
    protected function onFieldsVersion()
    {
        return 0;
    }

    /**
     * 获取sql缓存的redis ID，子类可覆写改方法使用指定的redis保存数据
     */
    protected function onGetRedisId()
    {
        return self::$sSqlRedisList[$this->redisConnection];
    }

    /**
     * 设置sql缓存类型和有效期, 有效期设置注意要同时兼顾缓存利用率和内存占用, 并尽可能的让旧版本的缓存数据尽早过期
     *
     * @param int $lineCacheExpire  主键和唯一键查询的数据缓存有效期, 如果单条数据很少变化, 此有效期可以设置久一点
     * @param int $shardCacheExpire 用于设置只在单个分区中查询的数据缓存有效期, 请根据单个分区数据变化频率设置此时间
     * @param int $tableCacheExpire 用于设置查询可能跨多个分区的缓存有效期, 对于数据变化较快的表, 此有效期不宜过长.
     */
    public function setCache($lineCacheExpire = 3600, $shardCacheExpire = 3600, $tableCacheExpire = 3600)
    {
        $this->mLineCacheExpireTime = $lineCacheExpire;
        $this->mLineCacheVerExpireTime = $lineCacheExpire * 20;
        $this->mShardCacheExpireTime = $shardCacheExpire;
        $this->mShardCacheVerExpireTime = $shardCacheExpire * 2;
        $this->mAllShardsCacheVerExpireTime = $shardCacheExpire * 20;
        $this->mTableCacheExpireTime = $tableCacheExpire;
        $this->mTableCacheVerExpireTime = $tableCacheExpire * 20;
    }

    /**
     * 部分后台任务会从数据库查询大量数据, 而这些数据有可能因量太大或者没有缓存价值而并不需要入缓存
     * 而来自用户的查询结果又需要入缓存. 调用此方法即可满足该需求
     */
    public function disableCache()
    {
        $this->mDisableCache = true;
    }

    public function enableCache()
    {
        $this->mDisableCache = false;
    }

    private function setShardKeys()
    {
        $fields = $this->onShardKeys();
        if (empty($fields)) {
            return;
        }
        if (!is_array($fields)) {
            throw new \Exception("setShardKeys() unexpire fields. {$fields}");
        }

        foreach ($fields as $name) {
            if (!is_string($name)) {
                throw new \Exception("setShardKeys() field should be string, error:{$name}");
            }
        }
        ksort($fields);
        $this->mShardKeys = $fields;
    }

    /**
     * 通过id查询并缓存到redis
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $id
     * @param array $columns
     *
     * @return bool
     */
    public function getById($id, $columns = ['*'])
    {
        if ($this->mDisableCache) {
            return parent::find($id, $columns);
        }
        $value = $this->getLineCacheById($id);
        if ($value) {
            return $value;
        }
        $value = parent::find($id);
        $this->setLineCacheById($id, $value);

        return $value;
    }

    /**
     * 通过数组条件查询
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $query
     *
     * @return array
     */
    public function getByParamArray($query)
    {
        if ($this->mDisableCache) {
            return parent::where($query)->get();
        }

        $value = $this->getLineCacheByUK($query);
        if ($value) {
            return $value;
        }
        $value = parent::where($query)->get();
        $this->setLineCacheByUK($query, $value);

        return $value;
    }

    /**
     * 根据ID进行删除
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $id
     *
     * @return bool|int
     */
    public function deleteById($id)
    {
        $data = $this->getById($id);
        if (!$data) {
            return true;
        }
        $result = parent::destroy($id);
        $this->deleteLineCache($data);
        $this->onDataChanged($data, false);
        $this->registerTransactionObserver('incrLineVersion');

        return $result;
    }

    /**
     * 根据条件删除多条记录
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $query
     *
     * @return bool
     */
    public function deleteByParamArray($query)
    {
        $data = $this->getByParamArray($query);
        if (!count($data)) {
            return true;
        }
        $result = parent::where($query)->delete();
        $this->deleteLineCache($data, false);
        $this->onDataChanged($data, false);
        $this->registerTransactionObserver('incrLineVersion');

        return $result;
    }

    /**
     * 更新单条记录，只会影响行级数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $id
     * @param $data
     *
     * @return bool
     */
    public function updateById($id, $data)
    {
        $cache = $this->getById($id);
        if (!$cache) {
            return false;
        }

        $result = parent::where('id', $id)->update($data);

        $this->deleteLineCache($cache);

        $changeShardOfItem = false;
        foreach ($this->mShardKeys as $shardKey) {
            if (isset($data[$shardKey]) && $data[$shardKey] != $cache[$shardKey]) {
                $changeShardOfItem = true;
                break;
            }
        }
        if ($changeShardOfItem) {
            /*可能将数据移动到了另一个分区, 需要将旧的分区和新的分区缓存都失效*/
            $this->onDataChanged([$data, $cache], true);
        } else {
            $this->onDataChanged($cache, false);
        }

        $this->registerTransactionObserver('incrLineVersion');

        return $result;
    }

    private function getCacheLevel($data)
    {
        if (empty($this->mShardKeys) || empty($data)) {
            return self::CACHE_LEVEL_TABLE;
        }

        $queryFromSingleShard = true;
        foreach ($this->mShardKeys as $keyName) {
            if (!isset($data[$keyName])) {
                $queryFromSingleShard = false;
                continue;
            }
            if (is_array($data[$keyName])) {
                $queryFromSingleShard = false;
                continue;
            }
        }

        if ($queryFromSingleShard) {
            return self::CACHE_LEVEL_SHARD;
        }

        return self::CACHE_LEVEL_TABLE;
    }

    private function extractShardKeyValue($query)
    {
        $shardKeysQuery = [];
        foreach ($this->mShardKeys as $keyName) {
            $shardKeysQuery[$keyName] = $query[$keyName];
        }

        return $shardKeysQuery;
    }

    /**
     * 为了防止由于方法名一样而造成的overload问题，在Dal中定义的方法全部加上Trait后缀
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $method
     * @param $args
     * @param $query
     *
     * @return mixed
     */
    private function doQuery($method, $args, $query)
    {
        if ($this->mDisableCache) {
            return call_user_func_array([$this, $method . 'Trait'], $args);
        }
        $cacheLevel = $this->getCacheLevel($query);
        switch ($cacheLevel) {
            case self::CACHE_LEVEL_SHARD:
                $shardKeyValue = $this->extractShardKeyValue($query);

                return $this->getFromSingleShard($method, $args, $shardKeyValue);
            case self::CACHE_LEVEL_TABLE:
                return $this->getFromTable($method, $args);
        }
    }

    /**
     * 查询单条数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $query
     *
     * @return mixed
     */
    public function getFirstByParamArray(array $query)
    {
        $method = __FUNCTION__;
        $args = func_get_args();

        return $this->doQuery($method, $args, $query);
    }

    /**
     * 查询多条数据 支持分页 排序
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $query
     * @param array $orderBy 默认['id' => 'desc']
     * @param int   $limit   默认取所有符合条件的记录
     * @param int   $offset
     *
     * @return mixed
     */
    public function getsBy(array $query, $orderBy = [], $limit = 0, $offset = 0)
    {
        $method = __FUNCTION__;
        $args = func_get_args();

        return $this->doQuery($method, $args, $query);
    }

    /**
     * 指定列查询多条数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $fields
     * @param $query
     * @param array $orderBy 默认['id' => 'desc']
     * @param int   $limit   默认取所有符合条件的记录
     * @param int   $offset
     *
     * @return mixed
     */
    public function getsColumnsBy(array $fields, $query, $orderBy = [], $limit = 0, $offset = 0)
    {
        $method = __FUNCTION__;
        $args = func_get_args();

        return $this->doQuery($method, $args, $query);
    }

    /**
     * 根据参数统计总数
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $query
     *
     * @return mixed
     */
    public function count($query = [])
    {
        $method = __FUNCTION__;
        $args = func_get_args();

        return $this->doQuery($method, $args, $query);
    }

    /**
     * 获取所有记录（慎用，全部记录将缓存到redis中）
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function getAll($columns = ['*'])
    {
        $method = __FUNCTION__;
        $args = func_get_args();

        return $this->doQuery($method, $args, []);
    }

    private function onDataChanged($data, $isMultiItem)
    {
        if ($this->mEnableTableCache) {
            $this->incrTableVersion();
            $this->registerTransactionObserver('incrTableVersion');
        }
        if (empty($this->mShardKeys)) {
            return;
        }

        $changedUnkownShard = false;
        $shardKeyValueArr = [];
        if (!$isMultiItem) {
            $data = [$data];
        }
        foreach ($data as $item) {
            $cacheLevel = $this->getCacheLevel($item);
            if (self::CACHE_LEVEL_SHARD == $cacheLevel) {
                $shardKeyValue = $this->extractShardKeyValue($item);
                $key = $this->getShardVersionKey($shardKeyValue);
                $shardKeyValueArr[$key] = $shardKeyValue;
            } else {
                $changedUnkownShard = true;
                break;
            }
        }

        /*有无法确定分区的数据插入，则把所有分区缓存都失效*/
        if ($changedUnkownShard) {
            $this->incrAllShardsVersion();
            $this->registerTransactionObserver('incrAllShardsVersion');

            return;
        }

        /*明确知道修改了哪些分区的数据，则只将这些分区的缓存失效*/
        foreach ($shardKeyValueArr as $shardKeyValue) {
            $this->incrShardVersion($shardKeyValue);
            $this->registerTransactionObserver('incrShardVersion', [$shardKeyValue]);
        }
    }

    /**
     * 插入一条新数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $data
     *
     * @return mixed
     */
    public function insertOne($data)
    {
        $method = __FUNCTION__;
        $args = func_get_args();
        $result = call_user_func_array([$this, $method . 'Trait'], $args);
        if (!$result) {
            return $result;
        }

        $this->onDataChanged($data, false);

        return $result;
    }

    /**
     * 插入数据, 必须包含全部字段
     *
     * @param array $data
     *
     * @return bool|int
     */
    public function multiInsert($data)
    {
        $method = __FUNCTION__;
        $args = func_get_args();
        $result = call_user_func_array([$this, $method . 'Trait'], $args);
        if (!$result) {
            return $result;
        }

        $this->onDataChanged($data, true);

        return $result;
    }

    /**
     * 删除数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $query
     *
     * @return mixed
     */
    public function deleteBy($query)
    {
        $method = __FUNCTION__;
        $args = func_get_args();
        $result = call_user_func_array([$this, $method . 'Trait'], $args);
        if (!$result) {
            return $result;
        }

        $this->onDataChanged($query, false);
        $this->incrLineVersion();

        return $result;
    }

    /**
     * 根据条件数组批量修改
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $data
     * @param $query
     *
     * @return mixed
     */
    public function updateBy($data, $query)
    {
        $method = __FUNCTION__;
        $args = func_get_args();
        $result = call_user_func_array([$this, $method . 'Trait'], $args);
        if (!$result) {
            return $result;
        }
        $changeShardOfItem = false;
        foreach ($this->mShardKeys as $shardKey) {
            if (isset($data[$shardKey])) {
                $changeShardOfItem = true;
                break;
            }
        }
        if ($changeShardOfItem) {
            /*可能将数据移动到了另一个分区, 需要将旧的分区和新的分区缓存都失效*/
            $this->onDataChanged([$data, $query], true);
        } else {
            $this->onDataChanged($query, false);
        }

        $this->incrLineVersion();

        return $result;
    }

    /**
     * 现网mysql配置的事务类型是READ-COMMITTED.要保证redis的sql缓存正确,需要
     * 在事务提交或回滚后,刷新(失效)涉及的sql缓存.
     */
    private function registerTransactionObserver($method, $callBackArgs = [])
    {
        $callBack['method'] = $method;
        $callBack['args'] = $callBackArgs;
        $this->mTransactionObserver[] = $callBack;
    }

    /**
     * 现网mysql配置的事务类型是READ-COMMITTED.要保证redis的sql缓存正确,需要
     * 在事务提交或回滚后,刷新(失效)涉及的sql缓存.
     */
    private function onTransactionFinish()
    {
        foreach ($this->mTransactionObserver as $cb) {
            $method = $cb['method'];
            $args = $cb['args'];
            call_user_func_array([$this, $method], $args);
        }

        $this->mTransactionObserver = [];
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     */
    public function beginTransaction()
    {
        DB::beginTransaction();
    }

    /**
     * 现网mysql配置的事务类型是READ-COMMITTED.要保证redis的sql缓存正确,需要
     * 在事务提交或回滚后,刷新(失效)涉及的sql缓存.
     *
     * @return bool
     */
    public function rollBack()
    {
        $result = DB::rollBack();
        $this->onTransactionFinish();

        return $result;
    }

    /**
     * 现网mysql配置的事务类型是READ-COMMITTED.要保证redis的sql缓存正确,需要
     * 在事务提交或回滚后,刷新(失效)涉及的sql缓存.
     *
     * @return bool
     */
    public function commit()
    {
        $result = DB::commit();
        $this->onTransactionFinish();

        return $result;
    }

    public static function allowOldCache($allow)
    {
        self::$sAllowOldCache = $allow;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    private function getFromTable($method, $args)
    {
        if (!$this->mEnableTableCache) {
            return call_user_func_array([$this, $method . 'Trait'], $args);
        }

        $fieldsVersion = $this->onFieldsVersion();
        $tableVersion = $this->getTableVersion();
        $key = $this->getCacheKey($method, $args);
        $value = $this->getRedis()->get($key);
        $locked = false;
        if ($value) {
            if ($fieldsVersion == $value[self::TAG_FIELDS_VERSION] && $value[self::TAG_VERSION_TABLE] == $tableVersion) {
                return $value[self::TAG_DATA];
            } else {
                if ($this->getRedis()->lock($key, 3)) {
                    $locked = true;
                } elseif (self::$sAllowOldCache) {
                    //避免集中访问数据库，第2个用户返回旧数据
                    //部分数据有页面缓存，为避免脏数据更新到最新版本的页面缓存中，需使用$sAllowOldCache加以控制
                    return $value[self::TAG_DATA];
                }
            }
        }
        if (self::INVALID_VERSION == $tableVersion) {
            $tableVersion = $this->initTableVersion();
        }
        $dbData = call_user_func_array([$this, $method . 'Trait'], $args);
        $value = [
            self::TAG_FIELDS_VERSION => $fieldsVersion,
            self::TAG_VERSION_TABLE => $tableVersion,
            self::TAG_DATA => $dbData,
        ];
        $this->getRedis()->set($key, $value, $this->mTableCacheExpireTime);
        if ($locked) {
            $this->getRedis()->unlock($key);
        }

        return $dbData;
    }

    private function getFromSingleShard($method, $args, $shardKeyValue)
    {
        if (empty($this->mShardKeys)) {
            return call_user_func_array([$this, $method . 'Trait'], $args);
        }

        $fieldsVersion = $this->onFieldsVersion();
        $shardVersion = $this->getShardVersion($shardKeyValue);
        $allShardsVersion = $this->getAllShardsVersion();

        $key = $this->getCacheKey($method, $args);
        $value = $this->getRedis()->get($key);
        $locked = false;
        if ($value) {
            if ($value[self::TAG_FIELDS_VERSION] == $fieldsVersion &&
                $value[self::TAG_VERSION_SHARD] == $shardVersion &&
                $value[self::TAG_VERSION_ALL_SHARDS] == $allShardsVersion) {
                return $value[self::TAG_DATA];
            } else {
                if ($this->getRedis()->lock($key, 3)) {
                    $locked = true;
                } elseif (self::$sAllowOldCache) {
                    //避免集中访问数据库，第2个用户返回旧数据
                    //部分数据有页面缓存，为避免脏数据更新到最新版本的页面缓存中，需使用$sAllowOldCache加以控制
                    return $value[self::TAG_DATA];
                }
            }
        }

        if (self::INVALID_VERSION == $shardVersion) {
            $shardVersion = $this->initShardVersion($shardKeyValue);
        }
        if (self::INVALID_VERSION == $allShardsVersion) {
            $allShardsVersion = $this->initAllShardsVersion();
        }
        $dbData = call_user_func_array([$this, $method . 'Trait'], $args);
        $value = [
            self::TAG_DATA => $dbData,
            self::TAG_FIELDS_VERSION => $fieldsVersion,
            self::TAG_VERSION_SHARD => $shardVersion,
            self::TAG_VERSION_ALL_SHARDS => $allShardsVersion,
        ];
        $this->getRedis()->set($key, $value, $this->mShardCacheExpireTime);

        if ($locked) {
            $this->getRedis()->unlock($key);
        }

        return $dbData;
    }

    private function getLineCacheById($id)
    {
        $version = $this->getLineVersion();
        if (!$version) {
            return false;
        }
        $fieldsVersion = $this->onFieldsVersion();

        $key = $this->getPrimaryCacheKey($id);
        $value = $this->getRedis()->get($key);
        if ($value[self::TAG_FIELDS_VERSION] == $fieldsVersion && $value[self::TAG_VERSION_LINE] == $version) {
            return $value[self::TAG_DATA];
        }

        return false;
    }

    private function setLineCacheById($id, $value)
    {
        $fieldsVersion = $this->onFieldsVersion();
        $version = $this->getLineVersion();
        if (self::INVALID_VERSION == $version) {
            $version = $this->initLineVersion();
        }

        $key = $this->getPrimaryCacheKey($id);
        $data = [
            self::TAG_FIELDS_VERSION => $fieldsVersion,
            self::TAG_VERSION_LINE => $version,
            self::TAG_DATA => $value,
        ];

        $this->getRedis()->set($key, $data, $this->mLineCacheExpireTime);
    }

    private function getLineCacheByUK($params)
    {
        $version = $this->getLineVersion();
        if (!$version) {
            return false;
        }
        $fieldsVersion = $this->onFieldsVersion();

        $key = $this->getUKCacheKey($params);
        $value = $this->getRedis()->get($key);
        if ($value[self::TAG_FIELDS_VERSION] == $fieldsVersion && $value[self::TAG_VERSION_LINE] == $version) {
            return $value[self::TAG_DATA];
        }

        return false;
    }

    private function setLineCacheByUK($params, $value)
    {
        $fieldsVersion = $this->onFieldsVersion();
        $version = $this->getLineVersion();
        if (self::INVALID_VERSION == $version) {
            $version = $this->initLineVersion();
        }

        $key = $this->getUKCacheKey($params);
        $data = [
            self::TAG_FIELDS_VERSION => $fieldsVersion,
            self::TAG_VERSION_LINE => $version,
            self::TAG_DATA => $value,
        ];

        $this->getRedis()->set($key, $data, $this->mLineCacheExpireTime);
    }

    private function deleteLineCache($data, $primary = 'id')
    {
        if ($primary) {
            $id = $data[$primary];
            $key = $this->getPrimaryCacheKey($id);
            $this->getRedis()->delete($key);
        }

        if ($data) {
            $key = $this->getUKCacheKey($data);
            $this->getRedis()->delete($key);
        }
    }

    private function getPrimaryCacheKey($id)
    {
        return parent::getTable() . ':' . self::TAG_LINE_GET_BY_ID . ':' . $id;
    }

    /**
     * 这里不能对$args进行strval转换，否则无法支持多数组条件
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $args
     *
     * @return string
     */
    private function getUKCacheKey($args)
    {
        $crcStr = dechex(crc32(json_encode($args)));

        return parent::getTable() . ':' . self::TAG_LINE_GET_BY_UK . ':' . $crcStr;
    }

    private function getCacheKey($method, $args)
    {
        $methodName = $method;
        if (strlen($methodName) > 8) {
            $methodName = dechex(crc32($methodName));
        }

        $crcStr = dechex(crc32(json_encode($args)));

        $key = parent::getTable() . ':' . $methodName . ':' . $crcStr;

        return $key;
    }

    private function getLineVersionKey()
    {
        return parent::getTable() . ':' . self::TAG_VERSION_LINE;
    }

    private function getLineVersion()
    {
        $key = $this->getLineVersionKey();
        $ver = $this->getRedis()->get($key);
        if ($ver) {
            return $ver;
        }

        return self::INVALID_VERSION;
    }

    private function initLineVersion()
    {
        $key = $this->getLineVersionKey();
        $version = rand(self::MIN_VERSION, self::MAX_VERSION);
        $this->getRedis()->set($key, $version, $this->mLineCacheVerExpireTime);

        return $version;
    }

    private function incrLineVersion()
    {
        $key = $this->getLineVersionKey();
        $ver = $this->getRedis()->incr($key);

        if ($ver > self::MAX_VERSION) {
            return $this->initLineVersion();
        }

        $this->getRedis()->expire($key, $this->mLineCacheVerExpireTime);

        return $ver;
    }

    private function getTableVersionKey()
    {
        return parent::getTable() . ':' . self::TAG_VERSION_TABLE;
    }

    private function getTableVersion()
    {
        $key = $this->getTableVersionKey();
        $ver = $this->getRedis()->get($key);
        if ($ver) {
            return $ver;
        }

        return self::INVALID_VERSION;
    }

    private function initTableVersion()
    {
        $key = $this->getTableVersionKey();
        $version = rand(self::MIN_VERSION, self::MAX_VERSION);
        $this->getRedis()->set($key, $version, $this->mTableCacheVerExpireTime);

        return $version;
    }

    private function incrTableVersion()
    {
        $key = $this->getTableVersionKey();
        $ver = $this->getRedis()->incr($key);

        if ($ver > self::MAX_VERSION) {
            return $this->initTableVersion();
        }

        $this->getRedis()->expire($key, $this->mTableCacheVerExpireTime);

        return $ver;
    }

    private function getShardVersionKey($shardKeyValue)
    {
        $shardTag = '';
        foreach ($shardKeyValue as $key => $value) {
            $shardTag = $shardTag . ':' . $key . ':' . $value;
        }
        if (strlen($shardTag) > 32) {
            $shardTag = md5($shardKeyValue);
        }

        return parent::getTable() . ':' . self::TAG_VERSION_SHARD . $shardTag;
    }

    private function initShardVersion($shardKeyValue)
    {
        $key = $this->getShardVersionKey($shardKeyValue);
        $version = rand(self::MIN_VERSION, self::MAX_VERSION);
        $this->getRedis()->set($key, $version, $this->mShardCacheVerExpireTime);

        return $version;
    }

    private function getShardVersion($shardKeyValue)
    {
        $key = $this->getShardVersionKey($shardKeyValue);
        $ver = $this->getRedis()->get($key);
        if ($ver) {
            return $ver;
        }

        return self::INVALID_VERSION;
    }

    private function incrShardVersion($shardKeyValue)
    {
        $key = $this->getShardVersionKey($shardKeyValue);
        $ver = $this->getRedis()->incr($key);

        if ($ver > self::MAX_VERSION) {
            return $this->initShardVersion($shardKeyValue);
        }

        $this->getRedis()->expire($key, $this->mShardCacheVerExpireTime);

        return $ver;
    }

    private function getAllShardsVersionKey()
    {
        return parent::getTable() . ':' . self::TAG_VERSION_ALL_SHARDS;
    }

    private function initAllShardsVersion()
    {
        $key = $this->getAllShardsVersionKey();
        $version = rand(self::MIN_VERSION, self::MAX_VERSION);
        $this->getRedis()->set($key, $version, $this->mAllShardsCacheVerExpireTime);

        return $version;
    }

    private function getAllShardsVersion()
    {
        $key = $this->getAllShardsVersionKey();
        $ver = $this->getRedis()->get($key);
        if ($ver) {
            return $ver;
        }

        return self::INVALID_VERSION;
    }

    private function incrAllShardsVersion()
    {
        $key = $this->getAllShardsVersionKey();
        $ver = $this->getRedis()->incr($key);

        if ($ver > self::MAX_VERSION) {
            return $this->initAllShardsVersion();
        }

        $this->getRedis()->expire($key, $this->mAllShardsCacheVerExpireTime);

        return $ver;
    }

    /**
     * 获得该表对应的redis对象
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return RedisService
     */
    private function getRedis()
    {
        return $this->redisUtil = new RedisService($this->mRedisId);
    }

    /**
     * 拼装原生orderBy
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $orderBy
     *
     * @return array
     */
    private function orderBySql($orderBy)
    {
        $orders = [];
        foreach ($orderBy as $key => $value) {
            if ($key == 'FIELD') {
                $orders[] = 'FIELD (`' . $value[0] . '`,' . implode(',', $value[1]) . ')';
            } else {
                $orders[] = '`' . $key . '`' . ' ' . $value;
            }
        }

        return $orders;
    }
}
