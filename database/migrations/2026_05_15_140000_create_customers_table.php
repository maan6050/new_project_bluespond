<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            // Nullable: guest customers have no login account.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 500)->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->enum('source', ['booking', 'import', 'manual', 'campaign', 'referral'])->default('booking');
            $table->enum('status', ['active', 'inactive', 'lost'])->default('active');
            $table->timestamp('last_visit_at')->nullable();
            $table->unsignedInteger('total_visits')->default(0);
            // Money stored as integer cents to match services.price.
            $table->unsignedBigInteger('total_spent')->default(0);
            $table->unsignedInteger('total_no_shows')->default(0);
            $table->unsignedBigInteger('lifetime_value')->default(0);
            $table->timestamp('inactive_since')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'last_visit_at']);
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
