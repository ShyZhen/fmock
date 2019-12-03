<?php

namespace App\Library\ModelToRedis;

trait ModelDal
{
    /**
     * 查询单条数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $params
     *
     * @return mixed
     */
    public function getFirstByParamArrayTrait($params)
    {
        return parent::where($params)->first();
    }

    /**
     * 查询多条数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $params
     * @param array $orderBy
     * @param int   $limit
     * @param int   $offset
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getsByTrait($params, $orderBy = [], $limit = 0, $offset = 0)
    {
        if (!$params || !is_array($params) || !is_array($orderBy)) {
            throw new \Exception("getsBy() args error: {$params}, {$orderBy}");
        }

        if (!count($orderBy)) {
            $orderBy = ['id' => 'desc'];
        }

        $orders = $this->orderBySql($orderBy);
        if (intval($limit) > 0) {
            return parent::where($params)->orderByRaw(implode(', ', $orders))->offset(intval($offset))->limit(intval($limit))->get();
        } else {
            return parent::where($params)->orderByRaw(implode(', ', $orders))->get();
        }
    }

    /**
     * 指定列查询多条数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $fields
     * @param $query
     * @param array $orderBy
     * @param int   $limit
     * @param int   $offset
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getsColumnsByTrait($fields, $query, $orderBy = [], $limit = 0, $offset = 0)
    {
        if (!$query || !is_array($orderBy)) {
            throw new \Exception("getsColumnsBy() args error: {$fields}, {$query}, {$orderBy}");
        }

        if (!count($orderBy)) {
            $orderBy = ['id' => 'desc'];
        }

        $orders = $this->orderBySql($orderBy);
        if (intval($limit) > 0) {
            return parent::select($fields)->where($query)->orderByRaw(implode(', ', $orders))->offset(intval($offset))->limit(intval($limit))->get();
        } else {
            return parent::select($fields)->where($query)->orderByRaw(implode(', ', $orders))->get();
        }
    }

    /**
     * 获取所有记录（慎用，全部记录将缓存到redis中）
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $columns
     *
     * @return \App\Models\BaseModel[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllTrait($columns = ['*'])
    {
        return parent::all($columns);
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
    public function insertOneTrait($data)
    {
        return parent::create($data);
    }

    /**
     * 批量插入
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $data
     *
     * @return bool
     */
    public function multiInsertTrait($data)
    {
        if (!is_array($data)) {
            return false;
        }

        return parent::insert($data);
    }

    /**
     * 删除数据
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $params
     *
     * @return mixed
     */
    public function deleteByTrait($params)
    {
        return parent::where($params)->delete();
    }

    /**
     * 批量更新
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $data
     * @param $params
     *
     * @return mixed
     */
    public function updateByTrait($data, $params)
    {
        return parent::where($params)->update($data);
    }

    /**
     * 根据参数统计总数
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $params
     *
     * @return mixed
     */
    public function countTrait($params = [])
    {
        return parent::where($params)->count();
    }
}
