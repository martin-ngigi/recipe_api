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
         Schema::create('app_states', function (Blueprint $table) {
            //$table->id();
            $table->uuid("id")->primary()->default(Str::uuid());
            $table->string('state');
            $table->string('description');
            $table->string('image')->default("");
            $table->enum('current',['true', 'false'])->default("false");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_states');
    }
};
