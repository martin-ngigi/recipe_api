<?php

use App\Models\AuthTypeEnum;
use App\Models\GenderEnum;
use App\Models\UserRoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_users', function (Blueprint $table) {
            $authTypes = array_map(fn($case) => $case->value, AuthTypeEnum::cases());
            $roles = array_map(fn($case) => $case->value,UserRoleEnum::cases());
            $genders = array_map(fn($case) => $case->value,GenderEnum::cases());

            $table->uuid('user_id')->primary(); // Use user_id as primary key
            $table->string('name');
            $table->string('email')->unique();
            $table->string('open_id')->unique();
            $table->string('avatar');
            $table->enum("role", $roles)->default(UserRoleEnum::Customer->value);
            $table->enum("gender", $genders)->nullable(); 
            $table->string('date_of_birth')->nullable();
            $table->string('phone')->nullable(); 
            $table->string('phone_complete')->nullable()->unique();
            $table->string('country_code')->nullable();
            $table->text('token')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
