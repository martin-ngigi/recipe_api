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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->uuid("ingredient_id")->primary()->default(Str::uuid());
            $table->string('name');
            $table->string('image');
            $table->string('quantity');
            $table->string('recipe_id');
            $table->timestamps();

            $table->foreign("recipe_id")
                ->references("recipe_id")
                ->on("recipes")
                ->onDelete("cascade");
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
