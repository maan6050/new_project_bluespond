<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `User::$fillable` and every consumer — UserVerificationService, UserService,
 * the SMS verification flow — reference a `phone` column, but SaaSyKit's
 * migration created it as `phone_number`. The mismatch makes any write fail
 * with "Unknown column 'phone'".
 *
 * Rename the column to `phone` to match the code (and the name 02-DATABASE-
 * SCHEMA.md specifies). Guarded so it converges databases that are already in
 * either state — a freshly migrated DB has `phone_number`, while some existing
 * databases already carry `phone`. `phone_number_verified_at` is consistent
 * everywhere and is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'phone_number') && ! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('phone_number', 'phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'phone') && ! Schema::hasColumn('users', 'phone_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('phone', 'phone_number');
            });
        }
    }
};
