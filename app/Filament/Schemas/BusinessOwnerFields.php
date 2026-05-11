<?php

namespace App\Filament\Schemas;

use App\Models\BusinessProfile;
use App\Models\User;
use Filament\Forms\Components\TextInput;

/**
 * Reusable Filament form-field builders for owner-related data on BusinessProfile.
 *
 * Used by both the admin and dashboard BusinessProfile resources so the
 * owner-email behavior stays consistent across both sides — change it here
 * and both forms update.
 */
class BusinessOwnerFields
{
    /**
     * Read-only "Owner Email" TextInput that mirrors the rest of the form's input style.
     *
     * The value is computed at form-hydration time from the tenant's owner and
     * is never persisted back (dehydrated(false)). Replaces the previous editable
     * `email` field in the Contact & Address section.
     */
    public static function ownerEmail(string $name = 'email', string $label = 'Email'): TextInput
    {
        return TextInput::make($name)
            ->label(__($label))
            ->email()
            ->disabled()
            ->dehydrated(false)
            ->afterStateHydrated(function (TextInput $component, ?BusinessProfile $record): void {
                $component->state(self::resolveOwner($record)?->email ?? '');
            });
    }

    /**
     * Resolve the primary owner — the user who created the tenant, falling back
     * to the first associated user if the creator is unavailable.
     */
    private static function resolveOwner(?BusinessProfile $record): ?User
    {
        if (! $record || ! $record->tenant) {
            return null;
        }

        return $record->tenant->creator ?? $record->tenant->users()->first();
    }
}
