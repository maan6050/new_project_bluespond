<?php

namespace App\Filament\Admin\Resources\BusinessProfiles\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlockedDatesRelationManager extends RelationManager
{
    protected static string $relationship = 'blockedDates';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    DatePicker::make('date')
                        ->label(__('Date'))
                        ->required()
                        ->native(false),
                    TextInput::make('reason')
                        ->label(__('Reason'))
                        ->placeholder(__('e.g. Holiday, Vacation, Maintenance'))
                        ->maxLength(255),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->placeholder('—')
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('date')
            ->modelLabel(__('Blocked Date'));
    }
}
