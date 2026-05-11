<?php

use App\Constants\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill user_type = 'admin' for existing users where is_admin = 1.
 *
 * The Sprint 1 migration added the `user_type` column with default 'customer',
 * which incorrectly tagged pre-existing super-admin accounts as customers.
 * This migration corrects that for any user with is_admin = 1.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('is_admin', 1)
            ->where('user_type', UserType::CUSTOMER->value)
            ->update(['user_type' => UserType::ADMIN->value]);
    }

    public function down(): void
    {
        // Intentional no-op: we cannot safely determine the original user_type
        // of admins before this backfill, so reverting could introduce wrong data.
    }
};
