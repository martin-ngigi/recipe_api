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
        Schema::create('user_notification_settings', function (Blueprint $table) {
            // $table->id();
            $table->uuid("id")->primary()->default(Str::uuid());
            $table->string('open_id');
            
            $table->enum("in_app", ["true", "false"])->default("true");
            $table->enum("email", ["true", "false"])->default("true");
            $table->enum("sms", ["true", "false"])->default("true");

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
        Schema::dropIfExists('user_notification_settings');
    }
};
