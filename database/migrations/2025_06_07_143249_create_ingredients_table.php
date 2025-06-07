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
            $table->string('recipe_id');
            $table->string("name");
            $table->string("image");
            $table->string("quantity");
            $table->string("unit")->default("");
            $table->timestamps();

            $table->foreign("recipe_id")  // product_id is defined in this table
            ->references("recipe_id") // product_id is defined in recipe table
            ->on("recipes") // this is the table name
            ->onDelete("cascade"); // This line sets up cascade deletion

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
