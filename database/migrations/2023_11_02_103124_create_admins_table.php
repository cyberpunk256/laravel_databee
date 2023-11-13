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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('role')->default(1)->nullable(); // config('constant.enums.roles')
            $table->bigInteger('group_id')->nullable()->unsigned()->comment('グループID');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            $table->tinyInteger('pref')->nullable(); 
            $table->string('init_lat')->nullable();
            $table->string('init_long')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
