<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/23
 * Time: 14:17
 */

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->user->create($data);
    }

    public function delete($id)
    {
        //return $this->find($id)->delete();
    }

    public function update(array $data, $id)
    {

    }

    public function find($id)
    {
        return $this->user->find($id);
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param $email
     * @return mixed
     */
    public function getFirstUserByEmail($email)
    {
        return $this->user->where('email', $email)->first();
    }
}