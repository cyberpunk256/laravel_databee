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
        Schema::create('captures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned()->comment('ユーザーID');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('media_id')->nullable()->unsigned()->comment('メディアID');
            $table->foreign('media_id')->references('id')->on('medias')->onDelete('cascade');

            $table->string('url')->nullable();
            $table->double('playtime', 10, 2)->nullable();
            $table->float('rotation', 8, 2)->nullable();
            $table->float('scale', 8, 2)->nullable();
            $table->double('lat', 11, 8)->nullable();
            $table->double('long', 11, 8)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captures');
    }
};
