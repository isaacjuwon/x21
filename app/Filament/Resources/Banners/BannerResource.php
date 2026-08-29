<?php

namespace App\Filament\Resources\Banners;

use App\Enums\Banners\BannerLocation;
use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Models\Banner;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Photo;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Banner Details')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Admin Title')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Internal label — not shown to users.'),

                    Select::make('location')
                        ->label('Location')
                        ->options(BannerLocation::class)
                        ->required(),
                ]),

            Section::make('Content')
                ->columnSpanFull()
                ->schema([
                    SpatieMediaLibraryFileUpload::make('image')
                        ->label('Banner Image')
                        ->collection('image')
                        ->disk('public')
                        ->visibility('public')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('8:3')
                        ->imageResizeTargetWidth('800')
                        ->imageResizeTargetHeight('300')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->maxSize(2048)
                        ->columnSpanFull(),

                    RichEditor::make('content')
                        ->label('HTML Content')
                        ->helperText('Optional. Supports text, links, and basic formatting.')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('banner-attachments')
                        ->fileAttachmentsVisibility('public')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline',
                            'bulletList', 'orderedList',
                            'link', 'h2', 'h3',
                        ])
                        ->columnSpanFull(),
                ]),

            Section::make('Call to Action')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('link_url')
                        ->label('Link URL')
                        ->url()
                        ->nullable()
                        ->placeholder('https://…'),

                    TextInput::make('link_text')
                        ->label('Link Button Text')
                        ->nullable()
                        ->maxLength(60)
                        ->placeholder('Learn more'),
                ]),

            Section::make('Scheduling & Visibility')
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->label('Start At')
                        ->nullable()
                        ->helperText('Leave blank to show immediately.'),

                    DateTimePicker::make('ends_at')
                        ->label('End At')
                        ->nullable()
                        ->helperText('Leave blank to show indefinitely.')
                        ->after('starts_at'),

                    TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first.'),
                ]),

            Grid::make(1)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active')
                        ->inline(false)
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Image')
                    ->collection('image')
                    ->conversion('thumb')
                    ->width(80)
                    ->height(30)
                    ->extraImgAttributes(['class' => 'rounded object-cover']),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('location')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBanners::route('/'),
        ];
    }
}
