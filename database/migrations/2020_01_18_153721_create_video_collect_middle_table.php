<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoCollectMiddleTable extends Migration
{
    /**
     * 视频集与视频素材的中间表 多对多关系 素材可以重复发布
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_collect_middle', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('video_id');
            $table->unsignedInteger('video_item_id');
            $table->integer('sort_index')->default(1);    // 用户自行输入，自行控制
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
        Schema::dropIfExists('video_collect_middle');
    }
}
