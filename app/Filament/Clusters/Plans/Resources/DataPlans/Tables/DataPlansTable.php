<?php

namespace App\Filament\Clusters\Plans\Resources\DataPlans\Tables;

use App\Models\DataPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class DataPlansTable
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
                    ->label('Network')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),
                TextColumn::make('api_code')
                    ->label('API Code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('brand_id')
                    ->label('Network')
                    ->relationship('brand', 'name'),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(
                        fn () => DataPlan::query()
                            ->whereNotNull('type')
                            ->distinct()
                            ->orderBy('type')
                            ->pluck('type', 'type')
                    ),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->defaultSort('brand_id')
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
