<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2020/1/20
 * Time: 9:11
 */
namespace App\Http\Controllers\Admin;

use App\Repositories\Eloquent\UserRepository;
use App\Http\Controllers\Admin\Base\AdminBaseController;

class UserController extends AdminBaseController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
}
