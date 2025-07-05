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
        Schema::create('all_rates', function (Blueprint $table) {
            $table->uuid("rate_id")->primary()->default(Str::uuid());
            $table->string("ratee_id");
            $table->string("rater_id");
            $table->double("rating")->default(0.0);
            $table->text("comment")->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign("ratee_id")
                ->references("open_id")
                ->on("app_users")
                ->onDelete("cascade");

            $table->foreign("rater_id")
                ->references("open_id")
                ->on("app_users") 
                ->onDelete("cascade");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('all_rates');
    }
};
