<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersFollowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 用户粉丝系统表
        Schema::create('users_follow', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('master_user_id')->index();             // 被关注者
            $table->unsignedInteger('following_user_id')->index();          // 关注动作的发出者，成为master的粉丝
            $table->enum('both_status', ['yes', 'none'])->default('none');  // 互粉状态
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
        Schema::dropIfExists('users_follow');
    }
}
