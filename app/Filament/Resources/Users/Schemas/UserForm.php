<?php

namespace App\Filament\Resources\Users\Schemas;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->nullable(),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create')
                            ->visible(fn () => auth()->user()?->hasRole('super_admin')),
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
