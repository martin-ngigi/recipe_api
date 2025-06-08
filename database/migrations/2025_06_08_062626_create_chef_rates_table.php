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
        Schema::create('chef_rates', function (Blueprint $table) {
            $table->uuid(column: "rate_id")->primary()->default(Str::uuid());
            $table->string("chef_id");
            $table->string("open_id");
            $table->double("rating")->default(0.0);
            $table->text("comment")->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign("chef_id")
                ->references("chef_id")
                ->on("chefs")
                ->onDelete("cascade");

            $table->foreign("open_id")
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
        Schema::dropIfExists('chef_rates');
    }
};
