<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // （问答类型）文章的回答表 为一对多关系（富文本，与post文章表类似，共用一个评论表）
        Schema::create('answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 64)->index();                                             // 代替id暴露在外
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('post_id')->index();                                   // 关联文章ID 一对多
            $table->string('title', 128)->default('');
            $table->string('summary', 128)->default('');                                    // 摘要
            $table->string('poster', 128)->default('');                                     // 第一幅海报图片
            $table->text('content');
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
        Schema::dropIfExists('answers');
    }
}
