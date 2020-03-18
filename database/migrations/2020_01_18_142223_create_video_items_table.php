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
            $table->string('url', 128)->default('');                        // 视频源地址
            $table->string('hls_url', 128)->default('');                    // 视频切片后的地址
            $table->enum('is_free', ['yes', 'none'])->default('yes');      // 是否免费
            $table->enum('is_publish', ['yes', 'none'])->default('none');  // 每个视频一个素材，发布后才可以上架，发布后不得更改
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
