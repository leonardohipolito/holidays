<?php

use App\Models\Holiday;
use App\Models\Participant;
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
        Schema::create('holiday_participant', function (Blueprint $table) {
            $table->foreignIdFor(Holiday::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(Participant::class)
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_participant');
    }
};
