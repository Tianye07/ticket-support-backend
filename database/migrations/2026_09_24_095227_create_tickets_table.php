<?php

use App\Constants\GeneralConstants;
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
        Schema::connection(GeneralConstants::DB_NAME)->create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('description', 350)->nullable();
            $table->string('priority', 25);
            $table->string('status', 25);
            $table->string('requester_name', 100);
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(GeneralConstants::DB_NAME)->dropIfExists('tickets');
    }
};
