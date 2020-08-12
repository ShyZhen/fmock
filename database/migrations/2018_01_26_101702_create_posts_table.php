<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 文章表
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 64)->index();   // 代替id暴露在外
            $table->unsignedInteger('user_id')->index();
//            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  需要匿名发布
            $table->string('title', 128)->default('');
            $table->string('summary', 128)->default('');                                    // 摘要
            $table->string('poster', 128)->default('');                                     // 第一幅海报图片
            $table->text('content');
            $table->enum('type', ['share', 'question', 'dynamite', 'friend', 'recruit']);  // 分享，问答，爆料，相亲，招聘
            $table->unsignedInteger('collect_num')->default(0);                             // 被收藏数量
            $table->unsignedInteger('comment_num')->default(0);                            // 被评论数量
            $table->unsignedInteger('like_num')->default(0);
            $table->unsignedInteger('dislike_num')->default(0);
            $table->enum('deleted', ['yes', 'none'])->default('none');
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
        Schema::dropIfExists('posts');
    }
}
