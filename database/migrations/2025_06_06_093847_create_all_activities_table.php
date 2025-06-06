<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('all_activities', function (Blueprint $table) {
            //$table->id();
            $table->uuid("activity_id")->primary()->default(Str::uuid());
            $table->text('message');
            $table->string('status')->nullable();
            $table->string('feature')->nullable();
            $table->string('open_id')->nullable();
            $table->integer('admin_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('all_activities');
    }
};
