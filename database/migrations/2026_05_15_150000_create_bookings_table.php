<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('booking_reference', 20)->unique(); // human-readable: "BLU-A1B2C3"
            $table->enum('status', [
                'pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled',
            ])->default('pending');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedInteger('duration_minutes');
            // Money stored as integer cents to match services.price.
            $table->unsignedInteger('price');
            $table->unsignedInteger('deposit_amount')->default(0);
            $table->boolean('deposit_paid')->default(false);
            $table->enum('source', ['online', 'phone', 'walk_in', 'recovery', 'waitlist'])->default('online');
            $table->text('notes')->nullable();          // customer notes
            $table->text('internal_notes')->nullable(); // staff notes
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->enum('cancelled_by', ['customer', 'business'])->nullable();
            // Self-referencing: points to the original booking when rescheduled.
            $table->foreignId('rescheduled_from')->nullable()->constrained('bookings')->nullOnDelete();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'start_time']);
            $table->index(['staff_member_id', 'start_time']);
            $table->index('customer_id');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
