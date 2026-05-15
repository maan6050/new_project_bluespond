<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates unique slugs that respect soft-deleted records.
 *
 * Soft-deleted rows still occupy the unique slug constraint, so we check
 * `withTrashed()` to avoid collisions when restoring.
 *
 * Requires the model to use the `SoftDeletes` trait and have a `slug` column.
 */
trait HasUniqueSlug
{
    public static function generateUniqueSlug(string $source, ?int $excludeId = null): string
    {
        $base = Str::slug($source) ?: 'item';
        $slug = $base;
        $counter = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->where('id', '!=', $excludeId ?? 0)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
