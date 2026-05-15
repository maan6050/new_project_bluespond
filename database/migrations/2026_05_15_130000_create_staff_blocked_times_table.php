<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_blocked_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['staff_member_id', 'start_datetime', 'end_datetime'], 'idx_staff_range');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_blocked_times');
    }
};
