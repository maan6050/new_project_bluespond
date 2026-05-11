<?php

namespace App\Filament\Admin\Resources\BusinessProfiles;

use App\Constants\BusinessVertical;
use App\Filament\Admin\Resources\BusinessProfiles\Pages\CreateBusinessProfile;
use App\Filament\Admin\Resources\BusinessProfiles\Pages\EditBusinessProfile;
use App\Filament\Admin\Resources\BusinessProfiles\Pages\ListBusinessProfiles;
use App\Filament\Admin\Resources\BusinessProfiles\RelationManagers\BlockedDatesRelationManager;
use App\Filament\Admin\Resources\BusinessProfiles\RelationManagers\HoursRelationManager;
use App\Filament\Admin\Resources\BusinessProfiles\RelationManagers\ServicesRelationManager;
use App\Filament\Admin\Resources\BusinessProfiles\RelationManagers\SocialLinksRelationManager;
use App\Filament\Schemas\BusinessOwnerFields;
use App\Models\BusinessCategory;
use App\Models\BusinessProfile;
use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BusinessProfileResource extends Resource
{
    protected static ?string $model = BusinessProfile::class;

    public static function getNavigationGroup(): ?string
    {
        return __('Bluespond');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Businesses');
    }

    public static function getModelLabel(): string
    {
        return __('Business');
    }

    public static function getNavigationLabel(): string
    {
        return __('Businesses');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->columns(2)
                    ->components([
                        Select::make('tenant_id')
                            ->label(__('Tenant'))
                            ->helperText(__('The Bluespond tenant that owns this business. One business per tenant.'))
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('category_id')
                            ->label(__('Category'))
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('vertical')->orderBy('sort_order'),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('business_name')
                            ->label(__('Business Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->helperText(__('Public URL slug. Leave empty to generate from name.'))
                            ->dehydrateStateUsing(fn ($state, Get $get, ?BusinessProfile $record) => empty($state)
                                ? BusinessProfile::generateUniqueSlug($get('business_name'), $record?->id)
                                : Str::slug($state)
                            )
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->unique(ignoreRecord: true),

                        Select::make('vertical')
                            ->label(__('Vertical'))
                            ->options(collect(BusinessVertical::cases())->mapWithKeys(
                                fn (BusinessVertical $case) => [$case->value => __(Str::headline($case->value))]
                            )->all())
                            ->default(BusinessVertical::APPOINTMENTS->value)
                            ->required(),

                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Contact & Address'))
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(20),

                        BusinessOwnerFields::ownerEmail(),

                        TextInput::make('address_line_1')
                            ->label(__('Address Line 1'))
                            ->maxLength(255),

                        TextInput::make('address_line_2')
                            ->label(__('Address Line 2'))
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label(__('City'))
                            ->maxLength(100),

                        TextInput::make('state')
                            ->label(__('State'))
                            ->maxLength(100),

                        TextInput::make('zip_code')
                            ->label(__('ZIP Code'))
                            ->maxLength(20),

                        TextInput::make('country')
                            ->label(__('Country (ISO-2)'))
                            ->default('US')
                            ->maxLength(2),

                        TextInput::make('latitude')
                            ->label(__('Latitude'))
                            ->numeric()
                            ->step(0.00000001),

                        TextInput::make('longitude')
                            ->label(__('Longitude'))
                            ->numeric()
                            ->step(0.00000001),
                    ]),

                Section::make(__('Locale & Settings'))
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        TextInput::make('timezone')
                            ->label(__('Timezone'))
                            ->default('America/New_York')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('currency')
                            ->label(__('Currency (ISO-4217)'))
                            ->default('USD')
                            ->required()
                            ->maxLength(3),

                        KeyValue::make('settings')
                            ->label(__('Booking Settings'))
                            ->keyLabel(__('Setting'))
                            ->valueLabel(__('Value'))
                            ->helperText(__('booking_buffer_minutes, max_advance_booking_days, cancellation_policy_hours, allow_guest_booking, require_deposit, deposit_percentage, auto_confirm_bookings, reminder_hours_before'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Media'))
                    ->columns(2)
                    ->collapsible()
                    ->components([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->label(__('Logo'))
                            ->collection(BusinessProfile::MEDIA_LOGO)
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096),

                        SpatieMediaLibraryFileUpload::make('cover')
                            ->label(__('Cover Image'))
                            ->collection(BusinessProfile::MEDIA_COVER)
                            ->image()
                            ->imageEditor()
                            ->maxSize(8192),
                    ]),

                Section::make(__('Status'))
                    ->columns(2)
                    ->components([
                        Toggle::make('is_published')
                            ->label(__('Published'))
                            ->helperText(function (?BusinessProfile $record): string {
                                if (! $record || ! $record->exists) {
                                    return __('Save the business first, then enable publishing once setup is complete.');
                                }

                                $blockers = $record->publishBlockers();

                                if ($blockers === []) {
                                    return __('When on, the business is visible on the marketplace and accepts public bookings.');
                                }

                                return '⚠ '.__('Cannot publish until you add: ').implode(', ', $blockers).'.';
                            })
                            ->disabled(fn (?BusinessProfile $record): bool => $record !== null && $record->exists && ! $record->canPublish())
                            ->default(false),

                        Toggle::make('is_featured')
                            ->label(__('Featured'))
                            ->helperText(__('Highlights the business on the marketplace homepage.'))
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label('')
                    ->collection(BusinessProfile::MEDIA_LOGO)
                    ->circular()
                    ->size(40),

                TextColumn::make('business_name')
                    ->label(__('Business'))
                    ->description(fn (BusinessProfile $record): ?string => $record->slug)
                    ->searchable(['business_name', 'slug'])
                    ->sortable(),

                TextColumn::make('tenant.name')
                    ->label(__('Tenant'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('vertical')
                    ->label(__('Vertical'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(Str::headline($state)))
                    ->sortable(),

                TextColumn::make('city')
                    ->label(__('Location'))
                    ->formatStateUsing(fn (BusinessProfile $record): string => trim(($record->city ?? '').($record->state ? ', '.$record->state : ''), ', ') ?: '—')
                    ->searchable(['city', 'state'])
                    ->toggleable(),

                ToggleColumn::make('is_published')
                    ->label(__('Published'))
                    ->getStateUsing(fn (BusinessProfile $record): bool => $record->isLive())
                    ->disabled(fn (BusinessProfile $record): bool => ! $record->isLive() && ! $record->canPublish())
                    ->tooltip(fn (BusinessProfile $record): ?string => (! $record->canPublish())
                        ? __('Cannot publish until you add: ').implode(', ', $record->publishBlockers())
                        : null
                    ),

                ToggleColumn::make('is_featured')
                    ->label(__('Featured')),

                TextColumn::make('average_rating')
                    ->label(__('Rating'))
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(),

                TextColumn::make('total_bookings')
                    ->label(__('Bookings'))
                    ->numeric()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('Joined'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vertical')
                    ->label(__('Vertical'))
                    ->options(collect(BusinessVertical::cases())->mapWithKeys(
                        fn (BusinessVertical $case) => [$case->value => __(Str::headline($case->value))]
                    )->all()),
                SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->options(fn () => BusinessCategory::orderBy('vertical')->orderBy('sort_order')->pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('tenant_id')
                    ->label(__('Tenant'))
                    ->options(fn () => Tenant::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                TernaryFilter::make('is_published')
                    ->label(__('Published')),
                TernaryFilter::make('is_featured')
                    ->label(__('Featured')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getRelations(): array
    {
        return [
            ServicesRelationManager::class,
            HoursRelationManager::class,
            BlockedDatesRelationManager::class,
            SocialLinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessProfiles::route('/'),
            'create' => CreateBusinessProfile::route('/create'),
            'edit' => EditBusinessProfile::route('/{record}/edit'),
        ];
    }
}
