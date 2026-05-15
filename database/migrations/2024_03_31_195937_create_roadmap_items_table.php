<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Note: the App\Constants\RoadmapItemStatus / RoadmapItemType enums were removed
// when the roadmap module was dropped (see 2026_05_05_110000_drop_blog_and_roadmap_tables).
// The default values below are inlined as plain strings so a fresh `migrate:fresh`
// can still run this migration. The roadmap_items table is dropped a few migrations
// later, so these defaults are never actually used.

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roadmap_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pending_approval');
            $table->string('type')->default('feature');
            $table->integer('upvotes')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_items');
    }
};
