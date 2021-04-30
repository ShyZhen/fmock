<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoItemsTable extends Migration
{
    /**
     * 视频素材
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 64)->index();
            $table->unsignedInteger('user_id')->index();
            $table->string('title', 128)->default('');
            $table->string('summary', 128)->default('');                    // 摘要
            $table->string('poster', 128)->default('');                     // 视频封面，默认使用视频第1s截图
            $table->string('hash', 128)->default('')->index();              // 视频hash，视频全名，用户七牛回调搜索
            $table->string('video_key', 128)->default('')->index();         // 视频key，视频全名
            $table->string('url', 128)->default('');                        // 视频源地址
            $table->string('hls_url', 128)->default('');                    // 视频切片后的地址 标清640*480
            $table->string('hls_hd_url', 128)->default('');                 // 视频切片后的地址 高清1280*720
            $table->tinyInteger('is_transcode')->default(2);                // 转码状态 0成功、1等待处理、2处理中、3处理失败、5任务被取消、6跳过、7无效
            $table->tinyInteger('is_free')->default(1);                     // 是否免费 1免费，0收费
            $table->tinyInteger('is_publish')->default(0);                  // 每个视频一个素材，发布后才可以上架，发布后不得更改
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
        Schema::dropIfExists('video_items');
    }
}
