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
        Schema::create('device_details', function (Blueprint $table) {
            // $table->id();
            $table->uuid("id")->primary()->default(Str::uuid());
            $table->string('device_id');
            $table->string('name');
            $table->string('model');
            $table->string('localized_model');
            $table->string('system_name');
            $table->string('version');
            $table->string('type');
            $table->string('open_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_details');
    }
};
