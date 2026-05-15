<?php

namespace App\Filament\Schemas;

use App\Models\BusinessProfile;
use App\Models\User;
use Filament\Forms\Components\TextInput;

/**
 * Reusable Filament form-field builders for BusinessProfile identity, contact,
 * and address fields.
 *
 * Single source of truth for the regex/length rules + friendly error copy that
 * are also enforced on the onboarding wizard. Used by both admin and owner
 * BusinessProfile resources so all three surfaces stay in lockstep — change
 * a rule here and every form updates.
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
     * Business name — must contain at least one letter so values like "12345"
     * are rejected. Mirrors the wizard's Step 1 businessName rule.
     */
    public static function businessName(): TextInput
    {
        return TextInput::make('business_name')
            ->label(__('Business Name'))
            ->required()
            ->maxLength(255)
            ->rules(['string', 'min:2', 'regex:/\p{L}/u'])
            ->validationMessages([
                'regex' => __('Business name must contain at least one letter.'),
                'min' => __('Business name must be at least 2 characters.'),
            ]);
    }

    /**
     * Phone — digits, spaces, and the symbols + - ( ) only. Optional.
     */
    public static function phone(): TextInput
    {
        return TextInput::make('phone')
            ->label(__('Phone'))
            ->tel()
            ->maxLength(20)
            ->rules(['nullable', 'string', 'min:7', 'regex:/^[+\d\s\-()]+$/'])
            ->validationMessages([
                'regex' => __('Phone can only contain digits, spaces, and the symbols + - ( ).'),
                'min' => __('Phone must be at least 7 characters.'),
            ]);
    }

    /**
     * City — letters/spaces and the punctuation . ' - , only, must contain a letter.
     */
    public static function city(): TextInput
    {
        return TextInput::make('city')
            ->label(__('City'))
            ->maxLength(100)
            ->rules(['nullable', 'string', 'regex:/^(?=.*\p{L})[\p{L}\s.\'\-,]+$/u'])
            ->validationMessages([
                'regex' => __('Enter a valid city name (letters, spaces, and . \' - , only).'),
            ]);
    }

    /**
     * State / Region — letters/spaces and the punctuation . ' - , only, must contain a letter.
     */
    public static function state(): TextInput
    {
        return TextInput::make('state')
            ->label(__('State'))
            ->maxLength(100)
            ->rules(['nullable', 'string', 'regex:/^(?=.*\p{L})[\p{L}\s.\'\-,]+$/u'])
            ->validationMessages([
                'regex' => __('Enter a valid state (letters, spaces, and . \' - only).'),
            ]);
    }

    /**
     * ZIP / Postal code — alphanumeric, optionally separated by a single space or hyphen.
     * Accepts 12345, 12345-6789, K1A 0B1, etc.
     */
    public static function zipCode(): TextInput
    {
        return TextInput::make('zip_code')
            ->label(__('ZIP Code'))
            ->maxLength(20)
            ->rules(['nullable', 'string', 'regex:/^[A-Za-z0-9]+([- ][A-Za-z0-9]+)*$/'])
            ->validationMessages([
                'regex' => __('Enter a valid postal code (e.g. 12345, 12345-6789, or K1A 0B1).'),
            ]);
    }

    /**
     * Country — ISO-3166-1 alpha-2 (two upper-case letters).
     */
    public static function country(): TextInput
    {
        return TextInput::make('country')
            ->label(__('Country (ISO-2)'))
            ->default('US')
            ->required()
            ->maxLength(2)
            ->rules(['string', 'size:2', 'regex:/^[A-Z]{2}$/'])
            ->validationMessages([
                'regex' => __('Country must be a 2-letter ISO code in upper case (e.g. US, CA, GB).'),
                'size' => __('Country must be exactly 2 letters.'),
            ]);
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
