<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('business_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country', 2)->default('US');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone', 50)->default('America/New_York');
            $table->string('currency', 3)->default('USD');
            $table->string('logo', 500)->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->decimal('average_rating', 2, 1)->default(0.0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('total_bookings')->default(0);
            $table->string('vertical')->default('appointments');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['city', 'state']);
            $table->index('is_published');
            $table->index(['latitude', 'longitude']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
