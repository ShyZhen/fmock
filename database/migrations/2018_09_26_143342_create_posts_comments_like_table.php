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
        // 用户对文章、回答、评论的赞和踩动作
        Schema::create('posts_comments_like', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('resource_id')->index();            // 资源ID(文章或评论)
            $table->enum('action', ['like', 'dislike']);                // 动作
            $table->enum('type', ['post', 'comment', 'answer', 'video', 'timeline']);        // 区分文章、回答、视频和评论
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
