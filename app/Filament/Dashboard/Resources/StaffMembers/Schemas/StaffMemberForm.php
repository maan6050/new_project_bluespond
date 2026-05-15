<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Details'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->placeholder(__('e.g. Jordan Lee')),

                        TextInput::make('title')
                            ->label(__('Title'))
                            ->helperText(__('Role shown to customers, e.g. Senior Stylist.'))
                            ->maxLength(100),

                        Textarea::make('bio')
                            ->label(__('Bio'))
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Contact'))
                    ->columns(2)
                    ->components([
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(20)
                            ->rule('regex:/^[+\d\s\-()]+$/')
                            ->validationMessages([
                                'regex' => __('Phone can only contain digits, spaces, and the symbols + - ( ).'),
                            ]),
                    ]),

                Section::make(__('Avatar'))
                    ->components([
                        FileUpload::make('avatar')
                            ->label(__('Avatar'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096)
                            ->disk('public')
                            ->directory(fn (): string => 'staff/'.Filament::getTenant()->uuid)
                            ->visibility('public'),
                    ]),

                Section::make(__('Visibility'))
                    ->columns(2)
                    ->components([
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->helperText(__('Inactive staff are hidden everywhere.'))
                            ->default(true),

                        Toggle::make('is_bookable')
                            ->label(__('Bookable'))
                            ->helperText(__('Off = active but customers cannot book them.'))
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->helperText(__('Lower numbers appear first.'))
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
