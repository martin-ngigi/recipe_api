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
        Schema::create('customer_notifications', function (Blueprint $table) {
            //$table->id();
            $table->uuid("notification_id")->primary()->default(Str::uuid());
            $table->string('open_id');

            $table->string('title');
            $table->text('message');
            $table->string('icon')->default("/icons/notification.png");
            $table->string('banner')->default("");
            $table->enum('is_read', ["true", "false"])->default("false");
            
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
        Schema::dropIfExists('customer_notifications');
    }
};
