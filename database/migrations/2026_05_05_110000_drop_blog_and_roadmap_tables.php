<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('roadmap_item_user_upvotes');
        Schema::dropIfExists('roadmap_items');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_post_categories');
    }

    public function down(): void
    {
        // Irreversible cleanup — Bluespond does not use blog or roadmap modules.
        // Restore from a SaaSykit baseline checkout if needed.
    }
};
