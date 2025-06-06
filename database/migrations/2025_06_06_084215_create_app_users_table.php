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
        Schema::create('app_users', function (Blueprint $table) {
             //$table->id();
            $table->uuid('user_id')->primary(); // Use user_id as primary key
            $table->string('name');
            $table->string('email')->unique();
            $table->enum("type", ["Email", "Google", "Apple", "Facebook", "Twitter", "Microsoft"])->default("Email"); // Email, Google, Apple auth
            $table->string('open_id')->unique();
            $table->string('avatar');
            $table->text('token')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
