<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/25
 * Time: 14:47
 */

namespace App\Repositories\Contracts;

interface RepositoryInterface
{
    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param int   $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*']);

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param array $data
     * @param $id
     *
     * @return mixed
     */
    public function update(array $data, $id);

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $id
     *
     * @return mixed
     */
    public function delete($id);

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $field
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = ['*']);
}
