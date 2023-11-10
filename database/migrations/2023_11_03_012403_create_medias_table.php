<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('admin_id')->nullable()->unsigned()->comment('管理者ID');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');

            $table->string('name')->nullable(); 
            $table->tinyInteger('type')->default(1)->comment('種別'); // config/const.php 3DMovie
            $table->double('video_time', 10, 2)->nullable()->comment('second'); 
            $table->string('media_path')->nullable(); 
            $table->string('gpx_path')->nullable(); 
            $table->double('image_lat', 11, 8)->nullable();
            $table->double('image_long', 11, 8)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
