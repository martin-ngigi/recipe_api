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
        Schema::create('device_f_c_m_tokens', function (Blueprint $table) {
            //$table->id();
            $table->uuid("device_fcm_id")->primary()->default(Str::uuid());

            $table->string('open_id');

            $table->string('android_token')->nullable();
            $table->string('ios_token')->nullable();
            $table->string('web_token')->nullable();

            $table->foreign('open_id')
            ->references('open_id')
            ->on('app_users')
            ->onDelete('cascade'); // This line sets up cascade deletion
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_f_c_m_tokens');
    }
};
