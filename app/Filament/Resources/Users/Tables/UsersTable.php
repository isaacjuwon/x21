<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(', ')
                    ->color('violet')
                    ->searchable(),

                TextColumn::make('loans_count')
                    ->label('Loans')
                    ->counts('loans')
                    ->sortable(),

                IconColumn::make('is_kyc_verified')
                    ->label('KYC Verified')
                    ->state(fn ($record) => $record->isKycVerified())
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('has_role')
                    ->label('Has Role')
                    ->query(fn (Builder $query) => $query->whereHas('roles')),

                Filter::make('no_role')
                    ->label('No Role')
                    ->query(fn (Builder $query) => $query->whereDoesntHave('roles')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
