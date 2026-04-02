<?php

namespace App\Filament\Clusters\Plans\Resources\EducationPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class EducationPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('brand.logo')
                    ->collection('logo')
                    ->circular()
                    ->label('Brand Logo'),
                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),
                TextColumn::make('duration')
                    ->sortable(),
                TextColumn::make('api_code')
                    ->searchable(),
                IconColumn::make('status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
