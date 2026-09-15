<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerPopupResource\Pages;
use App\Models\BannerPopup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class BannerPopupResource extends Resource
{
    protected static ?string $model = BannerPopup::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Photo;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return __('banner-popup::ui.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('banner-popup::ui.model_label_plural');
    }

    // -------------------------------------------------------------------------
    // Form
    // -------------------------------------------------------------------------

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Group::make()
                ->schema([

                    Section::make(__('banner-popup::ui.sections.images'))
                        ->description(__('banner-popup::ui.descriptions.images'))
                        ->icon(Heroicon::Photo)
                        ->columns(3)
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('banner_desktop')
                                ->label(__('banner-popup::ui.fields.banner_desktop'))
                                ->collection('banner_desktop')
                                ->disk('public')
                                ->visibility('public')
                                ->image()
                                ->imageEditor()
                                ->acceptedFileTypes(config('banner-popup.accepted_mime_types'))
                                ->helperText(__('banner-popup::ui.helpers.banner_desktop')),

                            SpatieMediaLibraryFileUpload::make('banner_tablet')
                                ->label(__('banner-popup::ui.fields.banner_tablet'))
                                ->collection('banner_tablet')
                                ->disk('public')
                                ->visibility('public')
                                ->image()
                                ->imageEditor()
                                ->acceptedFileTypes(config('banner-popup.accepted_mime_types'))
                                ->helperText(__('banner-popup::ui.helpers.banner_tablet')),

                            SpatieMediaLibraryFileUpload::make('banner_mobile')
                                ->label(__('banner-popup::ui.fields.banner_mobile'))
                                ->collection('banner_mobile')
                                ->disk('public')
                                ->visibility('public')
                                ->image()
                                ->imageEditor()
                                ->acceptedFileTypes(config('banner-popup.accepted_mime_types'))
                                ->helperText(__('banner-popup::ui.helpers.banner_mobile')),
                        ]),

                    Section::make(__('banner-popup::ui.sections.activation'))
                        ->description(__('banner-popup::ui.descriptions.activation'))
                        ->icon(Heroicon::Bolt)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label(__('banner-popup::ui.fields.name'))
                                ->required()
                                ->maxLength(150),

                            Forms\Components\Select::make('trigger')
                                ->label(__('banner-popup::ui.fields.trigger'))
                                ->options([
                                    'page_load' => __('banner-popup::ui.trigger_options.page_load'),
                                    'delay' => __('banner-popup::ui.trigger_options.delay'),
                                    'exit_intent' => __('banner-popup::ui.trigger_options.exit_intent'),
                                ])
                                ->default('page_load')
                                ->live()
                                ->required(),

                            Forms\Components\TextInput::make('trigger_delay')
                                ->label(__('banner-popup::ui.fields.trigger_delay'))
                                ->numeric()
                                ->minValue(1)
                                ->suffix(__('banner-popup::ui.suffix.seconds'))
                                ->visible(fn (Get $get): bool => $get('trigger') === 'delay'),
                        ]),

                    Section::make(__('banner-popup::ui.sections.display_rules'))
                        ->description(__('banner-popup::ui.descriptions.display_rules'))
                        ->icon(Heroicon::AdjustmentsHorizontal)
                        ->schema([
                            Forms\Components\TextInput::make('link_url')
                                ->label(__('banner-popup::ui.fields.link_url'))
                                ->url()
                                ->nullable(),

                            Forms\Components\Select::make('link_target')
                                ->label(__('banner-popup::ui.fields.link_target'))
                                ->options([
                                    '_self' => __('banner-popup::ui.link_target_options._self'),
                                    '_blank' => __('banner-popup::ui.link_target_options._blank'),
                                ])
                                ->default('_self'),

                            Forms\Components\Select::make('show_frequency')
                                ->label(__('banner-popup::ui.fields.show_frequency'))
                                ->options([
                                    0 => __('banner-popup::ui.frequency_options.always'),
                                    1 => __('banner-popup::ui.frequency_options.1'),
                                    3 => __('banner-popup::ui.frequency_options.3'),
                                    7 => __('banner-popup::ui.frequency_options.7'),
                                    30 => __('banner-popup::ui.frequency_options.30'),
                                    -1 => __('banner-popup::ui.frequency_options.once'),
                                ])
                                ->default(0),

                            Forms\Components\Select::make('target_pages')
                                ->label(__('banner-popup::ui.fields.target_pages'))
                                ->placeholder(__('banner-popup::ui.placeholders.target_pages'))
                                ->helperText(__('banner-popup::ui.helpers.target_pages'))
                                ->multiple()
                                ->searchable()
                                ->options(fn () => static::getPublicRouteOptions())
                                ->nullable(),
                        ]),
                ])
                ->columnSpan(['lg' => 2]),

            Group::make()
                ->schema([
                    Section::make(__('banner-popup::ui.sections.publication'))
                        ->description(__('banner-popup::ui.descriptions.publication'))
                        ->icon(Heroicon::Eye)
                        ->columns(2)
                        ->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label(__('banner-popup::ui.fields.is_active'))
                                ->helperText(__('banner-popup::ui.helpers.is_active'))
                                ->inline(false)
                                ->columnSpan(2),

                            Forms\Components\DateTimePicker::make('starts_at')
                                ->label(__('banner-popup::ui.fields.starts_at'))
                                ->nullable()
                                ->native(false),

                            Forms\Components\DateTimePicker::make('ends_at')
                                ->label(__('banner-popup::ui.fields.ends_at'))
                                ->nullable()
                                ->native(false)
                                ->after('starts_at'),
                        ]),
                ])
                ->columnSpan(['lg' => 1]),

        ])->columns(3);
    }

    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('banner-popup::ui.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('banner-popup::ui.fields.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('trigger')
                    ->label(__('banner-popup::ui.fields.trigger'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'page_load' => 'info',
                        'delay' => 'warning',
                        'exit_intent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'page_load' => __('banner-popup::ui.trigger_options.page_load'),
                        'delay' => __('banner-popup::ui.trigger_options.delay'),
                        'exit_intent' => __('banner-popup::ui.trigger_options.exit_intent'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('banner-popup::ui.fields.starts_at'))
                    ->dateTime('M j, Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('banner-popup::ui.fields.ends_at'))
                    ->dateTime('M j, Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\IconColumn::make('currently_active')
                    ->label(__('banner-popup::ui.columns.currently_active'))
                    ->state(fn (BannerPopup $record): bool => $record->isCurrentlyActive())
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBannerPopups::route('/'),
            'create' => Pages\CreateBannerPopup::route('/create'),
            'edit' => Pages\EditBannerPopup::route('/{record}/edit'),
        ];
    }

    public static function getRecordTitle(?Model $record): ?string
    {
        return $record?->name;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Curated list of pages admins can target with a banner popup.
     * Keys are route names (must match the actual registered routes).
     * Values are user-friendly labels shown in the Filament select.
     *
     * @return array<string, string>
     */
    protected static function getPublicRouteOptions(): array
    {
        return [
            // Dashboard
            'dashboard' => 'Dashboard',

            // KYC
            'kyc.index' => 'KYC (Profile Verification)',

            // Wallet
            'wallet.index' => 'Wallet — Overview',
            'wallet.transfer' => 'Wallet — Transfer',
            'wallet.withdraw' => 'Wallet — Withdraw',
            'wallet.fund' => 'Wallet — Fund',
            'wallet.transactions' => 'Wallet — Transactions',

            // Shares
            'shares.index' => 'Shares',

            // Services
            'services.airtime' => 'Services — Airtime Purchase',
            'services.data' => 'Services — Data Bundle',
            'services.cable' => 'Services — Cable TV',
            'services.electricity' => 'Services — Electricity Bill',
            'services.education' => 'Services — Education PINs',
        ];
    }
}
