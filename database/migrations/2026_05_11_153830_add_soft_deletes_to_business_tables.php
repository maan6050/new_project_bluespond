<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->softDeletes()->after('vertical');
        });

        Schema::table('business_categories', function (Blueprint $table) {
            $table->softDeletes()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('business_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
