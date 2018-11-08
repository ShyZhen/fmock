<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 图片表
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 255)->index();    // 代替id暴露在外
            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('url', 64)->defult('');
            $table->enum('deleted', ['yes', 'none'])->default('none');
            $table->unsignedInteger('like_num')->default(0);
            $table->unsignedInteger('dislike_num')->default(0);
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
        Schema::dropIfExists('images');
    }
}
