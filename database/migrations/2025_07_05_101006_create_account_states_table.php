<?php

use App\Models\AccountStatusEnum;
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
        Schema::create('account_states', function (Blueprint $table) {
            $statuses = array_map(fn($case) => $case->value, AccountStatusEnum::cases());

            $table->uuid("state_id")->primary()->default(Str::uuid());
            $table->enum('status', $statuses)->default(AccountStatusEnum::Active->value);
            $table->string('description')->nullable();
            $table->string('open_id');
            $table->timestamps();

            $table->foreign("open_id")  // open_id is defined in this table
            ->references("open_id") // open_id is defined in app_users table
            ->on("app_users") // this is the table name
            ->onDelete("cascade"); // This line sets up cascade deletion

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_states');
    }
};
