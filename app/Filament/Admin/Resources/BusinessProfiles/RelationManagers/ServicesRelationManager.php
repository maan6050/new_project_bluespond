<?php

namespace App\Filament\Admin\Resources\BusinessProfiles\RelationManagers;

use App\Models\Service;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Read-only services list on the admin Business edit page.
 *
 * Admin can SEE what services a business offers (for support context) but
 * cannot edit / delete / add them. Service management is owner territory —
 * owners manage their own services from the owner dashboard (Sprint 2).
 */
class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(40)
                    ->extraImgAttributes(['class' => 'rounded-lg']),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label(__('Category'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('duration_minutes')
                    ->label(__('Duration'))
                    ->formatStateUsing(fn (int $state): string => $state.' min')
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('Price'))
                    ->formatStateUsing(fn (int $state): string => '$'.number_format($state / 100, 2))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label(__('Active')),
                TernaryFilter::make('is_public')->label(__('Public')),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('sort_order')
            ->modelLabel(__('Service'))
            ->pluralModelLabel(__('Services'))
            ->emptyStateHeading(__('No services yet'))
            ->emptyStateDescription(__('Services are managed by the business owner from their dashboard.'));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
