<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            // Per-staff overrides. Null = fall back to the service's own value.
            // custom_price is stored in cents to match services.price.
            $table->unsignedInteger('custom_price')->nullable();
            $table->unsignedInteger('custom_duration')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'staff_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_staff');
    }
};
