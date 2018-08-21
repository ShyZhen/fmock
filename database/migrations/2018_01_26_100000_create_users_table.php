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
            $table->increments('id');
            $table->string('uuid', 255)->index();  // 代替id暴露在外
            $table->string('email', 64)->unique();               // 邮箱、手机等
            $table->string('password', 255);
            $table->rememberToken();
            $table->string('name', 32)->unique();               // 显示的用户昵称,第一次可改
            $table->string('avatar', 255)->default('');         // 没设置既使用默认头像
            $table->enum('gender', ['Male', 'Famale', 'secrecy'])->default('secrecy');
            $table->date('birthday')->default('1970-01-01');
            $table->string('reside_city', 32)->default('');         // 居住地
            $table->string('bio', 128)->default('');                // 个人一句话介绍 签名
            $table->enum('closure', ['none', 'yes'])->default('none'); // 用户状态，yes被封，无法登陆
            $table->enum('is_rename',['yes', 'none'])->default('yes'); // 判断是否可以改名，以后一块钱改一次
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
