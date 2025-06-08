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
        Schema::create('recipes', function (Blueprint $table) {
            $table->uuid("recipe_id")->primary()->default(Str::uuid());
            $table->string("name");
            $table->text("description");
            $table->json("ingredients");
            $table->string("image")->nullable();
            $table->string('chef_id');
            $table->text("instructions");
            $table->timestamps();

            $table->foreign("chef_id")  // chef_id is defined in this table
            ->references("chef_id") // chef_id is defined in chefs table
            ->on("chefs") // this is the table name
            ->onDelete("cascade"); // This line sets up cascade deletion

            /*
               $table->foreignId('chef_id')
                ->constrained('chefs', 'chef_id')
                ->onDelete('cascade'); // This line sets up cascade deletion
            */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
