<?php

namespace App\Filament\Resources\Users\Schemas;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('User Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create'),

                        Toggle::make('is_admin')
                            ->label('Administrator')
                            ->inline(false),
                    ]),
                ]),

            Section::make('Loan Level')
                ->schema([
                    Select::make('loan_level_id')
                        ->label('Loan Level')
                        ->relationship('loanLevel', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('No loan level assigned')
                        ->nullable(),
                ]),

            Section::make('Roles & Permissions')
                ->schema([
                    Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->options(fn () => Role::where('guard_name', Utils::getFilamentAuthGuard())->pluck('name', 'name'))
                        ->searchable()
                        ->preload()
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Select $component, $record) {
                            if ($record) {
                                $component->state($record->roles->pluck('name')->toArray());
                            }
                        })
                        ->saveRelationshipsUsing(function ($record, $state) {
                            $record->syncRoles($state ?? []);
                        }),
                ]),
        ]);
    }
}
