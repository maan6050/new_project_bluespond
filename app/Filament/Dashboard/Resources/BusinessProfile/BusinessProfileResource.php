<?php

namespace App\Filament\Dashboard\Resources\BusinessProfile;

use App\Constants\BusinessVertical;
use App\Filament\Admin\Resources\BusinessProfiles\RelationManagers\BlockedDatesRelationManager;
use App\Filament\Admin\Resources\BusinessProfiles\RelationManagers\HoursRelationManager;
use App\Filament\Admin\Resources\BusinessProfiles\RelationManagers\SocialLinksRelationManager;
use App\Filament\Dashboard\Resources\BusinessProfile\Pages\EditBusinessProfile;
use App\Filament\Dashboard\Resources\BusinessProfile\Pages\ListBusinessProfile;
use App\Models\BusinessProfile;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BusinessProfileResource extends Resource
{
    protected static ?string $model = BusinessProfile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = -100;

    public static function getPluralModelLabel(): string
    {
        return __('Business Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Business Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Business Settings');
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant, fn (Builder $q) => $q->where('tenant_id', $tenant->id));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->columns(2)
                    ->components([
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

                        Select::make('vertical')
                            ->label(__('Vertical'))
                            ->options(collect(BusinessVertical::cases())->mapWithKeys(
                                fn (BusinessVertical $case) => [$case->value => __(Str::headline($case->value))]
                            )->all())
                            ->default(BusinessVertical::APPOINTMENTS->value)
                            ->required(),

                        TextInput::make('business_name')
                            ->label(__('Business Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label(__('Public URL slug'))
                            ->helperText(__('Used in your public booking page URL.'))
                            ->disabled()
                            ->dehydrated(false),

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

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255),

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
                    ]),

                Section::make(__('Locale & Booking Settings'))
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

                Section::make(__('Branding'))
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

                Section::make(__('Visibility'))
                    ->components([
                        Toggle::make('is_published')
                            ->label(__('Published'))
                            ->helperText(__('When on, your business is visible to customers and accepts public bookings.'))
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
                    ->description(fn (BusinessProfile $record): ?string => $record->slug),

                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->placeholder('—'),

                TextColumn::make('city')
                    ->label(__('Location'))
                    ->formatStateUsing(fn (BusinessProfile $record): string => trim(($record->city ?? '').($record->state ? ', '.$record->state : ''), ', ') ?: '—'),

                TextColumn::make('is_published')
                    ->label(__('Published'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Live') : __('Draft'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HoursRelationManager::class,
            BlockedDatesRelationManager::class,
            SocialLinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessProfile::route('/'),
            'edit' => EditBusinessProfile::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
