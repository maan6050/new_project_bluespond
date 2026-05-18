<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * A staff member's ad-hoc time off — vacation, appointments, sick leave —
 * shown on the Staff Member edit page. Each entry is a one-off date-time
 * range, so unlike the weekly schedule these can be freely added and removed.
 */
class BlockedTimesRelationManager extends RelationManager
{
    protected static string $relationship = 'blockedTimes';

    protected static ?string $title = 'Time Off';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    DateTimePicker::make('start_datetime')
                        ->label(__('Starts'))
                        ->seconds(false)
                        ->native(false)
                        ->required(),

                    DateTimePicker::make('end_datetime')
                        ->label(__('Ends'))
                        ->seconds(false)
                        ->native(false)
                        ->required()
                        ->after('start_datetime'),

                    TextInput::make('reason')
                        ->label(__('Reason'))
                        ->placeholder(__('e.g. Vacation, Appointment, Sick leave'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('start_datetime')
                    ->label(__('Starts'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable(),

                TextColumn::make('end_datetime')
                    ->label(__('Ends'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable(),

                TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->placeholder('—')
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Time Off'))
                    ->successNotificationTitle(__('Time off added')),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotificationTitle(__('Time off updated')),
                DeleteAction::make()
                    ->successNotificationTitle(__('Time off removed')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->successNotificationTitle(__('Time off removed')),
            ])
            ->defaultSort('start_datetime', 'desc')
            ->modelLabel(__('Time Off'));
    }
}
