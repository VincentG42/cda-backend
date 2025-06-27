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
        Schema::create('user', function (Blueprint $table) {
            $table->id()->foreign('individual_stat.user_id');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('user_type')->index();
            $table->string('lastname');
            $table->string('firstname');
            $table->integer('team')->index()->nullable();
            $table->string('licence_number')->nullable();
            $table->tinyInteger('has_to_change_password');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
