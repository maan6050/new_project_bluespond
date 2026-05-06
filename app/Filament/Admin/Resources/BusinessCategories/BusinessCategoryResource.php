<?php

namespace App\Filament\Admin\Resources\BusinessCategories;

use App\Constants\BusinessVertical;
use App\Filament\Admin\Resources\BusinessCategories\Pages\CreateBusinessCategory;
use App\Filament\Admin\Resources\BusinessCategories\Pages\EditBusinessCategory;
use App\Filament\Admin\Resources\BusinessCategories\Pages\ListBusinessCategories;
use App\Models\BusinessCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BusinessCategoryResource extends Resource
{
    protected static ?string $model = BusinessCategory::class;

    public static function getNavigationGroup(): ?string
    {
        return __('Bluespond');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Business Categories');
    }

    public static function getModelLabel(): string
    {
        return __('Business Category');
    }

    public static function getNavigationLabel(): string
    {
        return __('Business Categories');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->helperText(__('Display name shown to businesses and customers (e.g. "Hair Salon").'))
                    ->required()
                    ->maxLength(100),

                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->helperText(__('Leave empty to generate slug automatically from name.'))
                    ->dehydrateStateUsing(function ($state, Get $get) {
                        if (empty($state)) {
                            $state = Str::slug($get('name'));
                            if (BusinessCategory::where('slug', $state)->exists()) {
                                $state .= '-'.Str::random(5);
                            }

                            return $state;
                        }

                        return Str::slug($state);
                    })
                    ->maxLength(100)
                    ->rules(['alpha_dash'])
                    ->unique(ignoreRecord: true),

                Select::make('vertical')
                    ->label(__('Vertical'))
                    ->helperText(__('Which Bluespond vertical this category belongs to.'))
                    ->options(collect(BusinessVertical::cases())->mapWithKeys(
                        fn (BusinessVertical $case) => [$case->value => __(Str::headline($case->value))]
                    )->all())
                    ->default(BusinessVertical::APPOINTMENTS->value)
                    ->required(),

                Select::make('parent_id')
                    ->label(__('Parent Category'))
                    ->helperText(__('Optional — pick a parent to create a sub-category.'))
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query, ?BusinessCategory $record) => $record
                            ? $query->where('id', '!=', $record->id)
                            : $query,
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('icon')
                    ->label(__('Icon'))
                    ->helperText(__('Heroicon identifier, e.g. "heroicon-o-scissors". Leave blank if not used.'))
                    ->maxLength(50)
                    ->nullable(),

                TextInput::make('sort_order')
                    ->label(__('Sort Order'))
                    ->helperText(__('Lower numbers appear first.'))
                    ->numeric()
                    ->default(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->helperText(__('Inactive categories are hidden from public listings and onboarding.'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('vertical')
                    ->label(__('Vertical'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(Str::headline($state)))
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('Parent'))
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('businessProfiles_count')
                    ->label(__('Businesses'))
                    ->counts('businessProfiles')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('Active')),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
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
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessCategories::route('/'),
            'create' => CreateBusinessCategory::route('/create'),
            'edit' => EditBusinessCategory::route('/{record}/edit'),
        ];
    }
}
