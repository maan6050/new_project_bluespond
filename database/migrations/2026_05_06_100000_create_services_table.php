<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->unsignedInteger('buffer_minutes')->default(0);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('deposit_amount')->default(0);
            $table->string('category', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('max_per_day')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('image', 500)->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
