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
        Schema::create('total_rates', function (Blueprint $table) {
            $table->uuid("rate_id")->primary()->default(Str::uuid());
            $table->string('open_id');
            $table->double("rating")->default(0.0); //average rating
            $table->integer('total_ratings')->default(0); //total number of ratings
            $table->timestamps();

            $table->foreign('open_id')
                ->references('open_id')
                ->on('app_users')
                ->onDelete('cascade'); // This line sets up cascade deletion

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_rates');
    }
};
