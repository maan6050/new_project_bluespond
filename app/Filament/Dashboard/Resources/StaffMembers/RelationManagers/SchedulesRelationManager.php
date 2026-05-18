<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\RelationManagers;

use App\Constants\DayOfWeek;
use App\Models\StaffSchedule;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

/**
 * The staff member's weekly working hours — one fixed row per day, shown on
 * the Staff Member edit page. Mirrors how business hours are managed.
 *
 * The seven rows are seeded with the staff member (see
 * StaffMember::createDefaultSchedules()), so the table is edit-only: a day's
 * hours are changed through a modal, but days cannot be added or removed.
 */
class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Weekly Schedule';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_available')
                    ->label(__('Available this day'))
                    ->helperText(__('Off = this person does not work on this day.'))
                    ->live()
                    ->columnSpanFull(),

                TimePicker::make('start_time')
                    ->label(__('Starts'))
                    ->seconds(false)
                    ->visible(fn ($get): bool => (bool) $get('is_available'))
                    ->required(fn ($get): bool => (bool) $get('is_available')),

                TimePicker::make('end_time')
                    ->label(__('Ends'))
                    ->seconds(false)
                    ->visible(fn ($get): bool => (bool) $get('is_available'))
                    ->required(fn ($get): bool => (bool) $get('is_available'))
                    ->after('start_time'),

                TimePicker::make('break_start')
                    ->label(__('Break starts'))
                    ->helperText(__('Optional. Leave both break fields empty for no break.'))
                    ->seconds(false)
                    ->visible(fn ($get): bool => (bool) $get('is_available'))
                    ->requiredWith('break_end'),

                TimePicker::make('break_end')
                    ->label(__('Break ends'))
                    ->seconds(false)
                    ->visible(fn ($get): bool => (bool) $get('is_available'))
                    ->after('break_start')
                    ->requiredWith('break_start'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label(__('Day'))
                    ->formatStateUsing(fn (int $state): string => DayOfWeek::from($state)->label())
                    ->weight('medium'),

                IconColumn::make('is_available')
                    ->label(__('Available'))
                    ->boolean(),

                TextColumn::make('hours')
                    ->label(__('Hours'))
                    ->state(fn (StaffSchedule $record): string => $record->is_available
                        ? self::formatTime($record->start_time).' – '.self::formatTime($record->end_time)
                        : __('Unavailable')),

                TextColumn::make('break')
                    ->label(__('Break'))
                    ->state(fn (StaffSchedule $record): string => $record->break_start
                        ? self::formatTime($record->break_start).' – '.self::formatTime($record->break_end)
                        : '—'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(fn (StaffSchedule $record): string => __('Edit :day', [
                        'day' => DayOfWeek::from($record->day_of_week)->label(),
                    ]))
                    ->successNotificationTitle(__('Schedule updated')),
            ])
            ->defaultSort('day_of_week')
            ->paginated(false);
    }

    /**
     * Render a stored TIME value (e.g. "09:00:00") in a friendly 12-hour form.
     */
    private static function formatTime(?string $time): string
    {
        return $time ? Carbon::parse($time)->format('g:i A') : '—';
    }
}
