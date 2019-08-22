<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsCommentsLikeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 用户对文章或评论的赞和踩动作
        // TODO 加入问答文章表
        Schema::create('posts_comments_like', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('resource_id')->index();  // 资源ID(文章或评论)
            $table->enum('action', ['like', 'dislike']);      // 动作
            $table->enum('type', ['post', 'comment']);        // 区分文章和评论
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
        Schema::dropIfExists('posts_comments_like');
    }
}
