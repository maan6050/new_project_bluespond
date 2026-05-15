<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only audit log of booking status transitions — created_at only.
        Schema::create('booking_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            // user_id of whoever changed it. No FK — matches the schema doc,
            // and the row should survive the user being removed.
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_history');
    }
};
