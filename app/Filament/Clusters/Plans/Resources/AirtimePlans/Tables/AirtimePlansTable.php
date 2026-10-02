<?php

namespace App\Filament\Clusters\Plans\Resources\AirtimePlans\Tables;

use App\Models\AirtimePlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AirtimePlansTable
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
                TextColumn::make('api_codes_summary')
                    ->label('API Codes')
                    ->state(function (AirtimePlan $record): string {
                        $pairs = $record->providerCodes
                            ->map(fn ($c) => "{$c->provider}: {$c->code}")
                            ->join('  ·  ');

                        return $pairs ?: '—';
                    })
                    ->searchable(
                        query: fn ($query, string $search) => $query
                            ->whereHas('providerCodes', fn ($q) => $q->where('code', 'like', "%{$search}%"))
                    ),
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
