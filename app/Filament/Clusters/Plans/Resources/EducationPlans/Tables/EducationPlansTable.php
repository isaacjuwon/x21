<?php

namespace App\Filament\Clusters\Plans\Resources\EducationPlans\Tables;

use App\Models\EducationPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable(),
                TextColumn::make('duration')
                    ->sortable(),
                TextColumn::make('api_codes_summary')
                    ->label('API Codes')
                    ->state(function (EducationPlan $record): string {
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
                    ->label('Brand')
                    ->relationship('brand', 'name'),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(
                        fn () => EducationPlan::query()
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
