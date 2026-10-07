<?php

declare(strict_types=1);

namespace App\Filament\Resources\Plans\Tables;

use App\Enums\Plans\ServiceType;
use App\Models\Plan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading(! app()->runningUnitTests())
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['brand.media', 'providerCodes']))
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge()
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('brand.logo')
                    ->collection('logo')
                    ->circular()
                    ->label('Brand'),
                TextColumn::make('brand.name')
                    ->label('Network / Brand')
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
                TextColumn::make('cost_price')
                    ->label('Cost Price')
                    ->money(fn () => Number::defaultCurrency())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('api_codes_summary')
                    ->label('API Codes')
                    ->state(function (Plan $record): string {
                        $pairs = $record->providerCodes
                            ->map(fn ($c) => "{$c->provider}: {$c->code}")
                            ->join('  ·  ');

                        return $pairs ?: ($record->api_code ?: '—');
                    })
                    ->searchable(
                        query: fn (Builder $query, string $search) => $query->where(function (Builder $q) use ($search) {
                            $q->where('api_code', 'like', "%{$search}%")
                                ->orWhereHas('providerCodes', fn (Builder $sub) => $sub->where('code', 'like', "%{$search}%"));
                        })
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
                SelectFilter::make('service_type')
                    ->label('Service Type')
                    ->options(
                        collect(ServiceType::cases())
                            ->mapWithKeys(fn (ServiceType $type): array => [$type->value => $type->getLabel()])
                            ->all()
                    ),
                SelectFilter::make('brand_id')
                    ->label('Brand / Network')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->defaultSort('id', 'desc')
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
