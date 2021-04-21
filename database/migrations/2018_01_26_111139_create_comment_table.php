<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 评论回复表，不能使用富文本，最多500个字
        // post_id改为resource_id，代替post_id和answer_id，这样可以把所有评论联系起来用一张表。
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['post', 'answer, video, timeline']);            // 区分是answer表还是post表
            $table->string('resource_uuid', 64)->index();
            $table->unsignedInteger('resource_id')->index();
            $table->unsignedInteger('parent_id')->default(0);    // 0代表评论主体，否则代表回复该comment
//            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->integer('user_id');
            $table->string('content', 256)->default('');
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
        Schema::dropIfExists('comments');
    }
}
