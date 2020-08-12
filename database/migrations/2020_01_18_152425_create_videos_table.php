<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideosTable extends Migration
{
    /**
     * 视频集
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 64)->index();
            $table->unsignedInteger('user_id')->index();
            $table->string('title', 128)->default('');
            $table->string('summary', 128)->default('');                    // 摘要
            $table->string('poster', 128)->default('');                     // 视频集封面
            $table->enum('is_free', ['yes', 'none'])->default('yes');       // 是否免费，只有视频集收费才判断素材是否免费(试看状态)；如果视频集免费，那么不判断素材的状态，全部免费
            $table->enum('is_release', ['yes', 'none', 'review'])->default('none');  // 上线后可观看、购买;上线流程：提交审核 -> 审核通过 -> 上线
            $table->unsignedInteger('collect_num')->default(0);                       // 被收藏数量
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
        Schema::dropIfExists('videos');
    }
}
