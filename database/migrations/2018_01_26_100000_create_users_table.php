<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 用户表
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 64)->index();                        // 代替id暴露在外
            $table->string('email', 64)->default('')->index();          // 登录邮箱
            $table->string('mobile', 64)->default('')->index();         // 登录手机
            $table->string('password', 255);
            $table->rememberToken();
            $table->string('name', 64)->index();                        // 显示的用户昵称,第一次可改
            $table->string('avatar', 255)->default('');                 // 没设置既使用默认头像
            $table->string('photo_wall', 255)->default('');             // 背景墙图片
            $table->enum('gender', ['male', 'female', 'secrecy'])->default('secrecy');
            $table->date('birthday')->default('1970-01-01');
            $table->string('reside_city', 32)->default('');             // 居住地
            $table->string('bio', 128)->default('');                    // 个人一句话介绍 签名
            $table->enum('closure', ['none', 'yes'])->default('none');  // 用户状态，yes被封，无法登陆
            $table->enum('is_rename', ['yes', 'none'])->default('yes'); // 判断是否可以改名，以后一块钱改一次

            $table->unsignedInteger('fans_num')->default(0);
            $table->unsignedInteger('followed_num')->default(0);

            $table->string('company', 64)->default('');                 // 公司
            $table->string('company_type', 64)->default('');            // 行业
            $table->string('position', 64)->default('');                // 职位

            $table->string('intro', 255)->default('');
            $table->string('qq', 32)->default('');
            $table->string('wechat', 64)->default('');
            $table->string('github', 64)->default('');
            $table->string('github_id', 32)->default('');
            $table->string('wechat_openid', 32)->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('users');
    }
}
