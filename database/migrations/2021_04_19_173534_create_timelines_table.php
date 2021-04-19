<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 朋友圈、微博那种图文，非富文本类型
        Schema::create('timelines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 64)->index();   // 代替id暴露在外
            $table->unsignedInteger('user_id')->index();
            $table->string('title', 255)->default('');
            $table->string('poster_list', 1280)->default('');
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
        Schema::dropIfExists('timelines');
    }
}
