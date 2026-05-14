<?php

namespace App\Filament\Dashboard\Resources\Services\Schemas;

use App\Models\Service;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Details'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Service Name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('e.g. Men\'s Haircut')),

                        TextInput::make('category')
                            ->label(__('Category'))
                            ->helperText(__('Optional grouping shown on your booking page (e.g. Haircuts, Color).'))
                            ->maxLength(100),

                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label(__('URL slug'))
                            ->helperText(__('Auto-generated from the service name. Used in booking URLs.'))
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?Service $record): bool => $record !== null && $record->exists),
                    ]),

                Section::make(__('Pricing'))
                    ->columns(2)
                    ->components([
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0)
                            ->formatStateUsing(fn (?int $state): ?float => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),

                        TextInput::make('deposit_amount')
                            ->label(__('Deposit'))
                            ->helperText(__('Optional. Customers pay this upfront to confirm a booking.'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0)
                            ->formatStateUsing(fn (?int $state): ?float => $state !== null ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state): int => (int) round(((float) ($state ?? 0)) * 100)),
                    ]),

                Section::make(__('Timing'))
                    ->columns(2)
                    ->components([
                        TextInput::make('duration_minutes')
                            ->label(__('Duration'))
                            ->required()
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(1440)
                            ->step(5)
                            ->default(30)
                            ->suffix(__('minutes')),

                        TextInput::make('buffer_minutes')
                            ->label(__('Buffer After'))
                            ->helperText(__('Gap left after each booking before the next slot opens.'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(240)
                            ->step(5)
                            ->default(0)
                            ->suffix(__('minutes')),

                        TextInput::make('max_per_day')
                            ->label(__('Max Per Day'))
                            ->helperText(__('Optional daily cap for this service. Leave blank for unlimited.'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(500),

                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->helperText(__('Lower numbers appear first on your booking page.'))
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make(__('Image'))
                    ->components([
                        FileUpload::make('image')
                            ->label(__('Service Image'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096)
                            ->disk('public')
                            ->directory(fn (): string => 'services/'.Filament::getTenant()->uuid)
                            ->visibility('public'),
                    ]),

                Section::make(__('Visibility'))
                    ->columns(2)
                    ->components([
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->helperText(__('Inactive services cannot be booked.'))
                            ->default(true),

                        Toggle::make('is_public')
                            ->label(__('Show on public booking page'))
                            ->helperText(__('Off = internal/walk-in only.'))
                            ->default(true),
                    ]),
            ]);
    }
}
