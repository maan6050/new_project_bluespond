<?php

namespace App\Filament\Admin\Resources\BusinessProfiles\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HoursRelationManager extends RelationManager
{
    protected static string $relationship = 'hours';

    private const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('day_of_week')
                        ->label(__('Day'))
                        ->options(collect(self::DAYS)->mapWithKeys(fn ($name, $idx) => [$idx => __($name)])->all())
                        ->required(),
                    Toggle::make('is_closed')
                        ->label(__('Closed all day'))
                        ->default(false)
                        ->live(),
                    TimePicker::make('open_time')
                        ->label(__('Opens'))
                        ->seconds(false)
                        ->visible(fn ($get) => ! $get('is_closed')),
                    TimePicker::make('close_time')
                        ->label(__('Closes'))
                        ->seconds(false)
                        ->visible(fn ($get) => ! $get('is_closed')),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label(__('Day'))
                    ->formatStateUsing(fn (int $state): string => __(self::DAYS[$state] ?? '—'))
                    ->sortable(),
                IconColumn::make('is_closed')
                    ->label(__('Closed'))
                    ->boolean(),
                TextColumn::make('open_time')
                    ->label(__('Opens'))
                    ->placeholder('—'),
                TextColumn::make('close_time')
                    ->label(__('Closes'))
                    ->placeholder('—'),
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
            ->defaultSort('day_of_week')
            ->modelLabel(__('Business Hours'));
    }
}
