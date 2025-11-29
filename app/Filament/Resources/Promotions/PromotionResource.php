<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promotions;

use App\Filament\Resources\Promotions\Pages\CreatePromotion;
use App\Filament\Resources\Promotions\Pages\EditPromotion;
use App\Filament\Resources\Promotions\Pages\ListPromotions;
use App\Models\Promotion;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Promocja';

    protected static ?string $pluralModelLabel = 'Promocje';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Tytuł')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, callable $set) {
                                $set('slug', Str::slug($state));
                            })
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Automatycznie generowany z tytułu'),

                        Forms\Components\Toggle::make('active')
                            ->label('Aktywna')
                            ->default(true)
                            ->helperText('Wyłącz aby ukryć promocję'),

                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Ważna od')
                            ->helperText('Pozostaw puste jeśli bez ograniczeń'),

                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Ważna do')
                            ->helperText('Pozostaw puste jeśli bez ograniczeń'),
                    ])
                    ->columns(2),

                Section::make('Treść promocji')
                    ->schema([
                        Forms\Components\RichEditor::make('body')
                            ->label('Opis promocji')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'link', 'bulletList', 'orderedList',
                                'h2', 'h3', 'blockquote',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull()
                            ->extraInputAttributes(['style' => 'min-height: 30rem;'])
                            ->helperText('Główna treść promocji. Obrazki dodaj przez bloki poniżej.'),
                    ]),

                Section::make('Zaawansowane bloki (opcjonalnie)')
                    ->schema([
                        Forms\Components\Builder::make('content')
                            ->label('Dodatkowe bloki')
                            ->blocks([

                                Forms\Components\Builder\Block::make('image')
                                    ->label('Zdjęcie')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->label('Zdjęcie')
                                            ->image()
                                            ->required()
                                            ->directory('promotions/images')
                                            ->maxSize(5120),

                                        Forms\Components\TextInput::make('alt')
                                            ->label('Tekst alternatywny (ALT)')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('caption')
                                            ->label('Podpis')
                                            ->maxLength(255),

                                        Forms\Components\Select::make('size')
                                            ->label('Rozmiar')
                                            ->options([
                                                'small' => 'Mały',
                                                'medium' => 'Średni',
                                                'large' => 'Duży',
                                                'full' => 'Pełna szerokość',
                                            ])
                                            ->default('large'),
                                    ]),

                                Forms\Components\Builder\Block::make('cta')
                                    ->label('Call to Action')
                                    ->icon('heroicon-o-cursor-arrow-ripple')
                                    ->schema([
                                        Forms\Components\TextInput::make('heading')
                                            ->label('Nagłówek')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Opis')
                                            ->rows(3)
                                            ->maxLength(500),

                                        Forms\Components\TextInput::make('button_text')
                                            ->label('Tekst przycisku')
                                            ->default('Skorzystaj teraz!')
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('button_url')
                                            ->label('Link przycisku')
                                            ->url(),

                                        Forms\Components\Select::make('style')
                                            ->label('Styl')
                                            ->options([
                                                'primary' => 'Podstawowy (niebieski)',
                                                'secondary' => 'Drugorzędny (szary)',
                                                'accent' => 'Akcentowy (zielony)',
                                            ])
                                            ->default('accent'),
                                    ]),

                                Forms\Components\Builder\Block::make('two_columns')
                                    ->label('Dwie kolumny')
                                    ->icon('heroicon-o-view-columns')
                                    ->schema([
                                        Forms\Components\RichEditor::make('left_column')
                                            ->label('Lewa kolumna')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'bulletList',
                                                'orderedList', 'h3',
                                            ]),

                                        Forms\Components\RichEditor::make('right_column')
                                            ->label('Prawa kolumna')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'bulletList',
                                                'orderedList', 'h3',
                                            ]),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->blockNumbers(false)
                            ->reorderable()
                            ->addActionLabel('Dodaj blok')
                            ->columnSpanFull()
                            ->helperText('Opcjonalne: dodaj galerie zdjęć, wideo, przyciski CTA'),
                    ])
                    ->collapsed(),

                Section::make('SEO')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Zdjęcie wyróżniające')
                            ->image()
                            ->directory('promotions/featured')
                            ->maxSize(5120),

                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta tytuł')
                            ->maxLength(60)
                            ->helperText('Zalecane: do 60 znaków'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta opis')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('Zalecane: do 160 znaków'),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('active')
                    ->label('Aktywna')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Ważna od')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Ważna do')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Promotion $record) => $record->isActiveAndValid() ? 'active' : 'inactive'
                    )
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktywna',
                        'inactive' => 'Nieaktywna',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ostatnia aktualizacja')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Aktywne')
                    ->query(fn ($query) => $query->active()),

                Tables\Filters\Filter::make('valid')
                    ->label('Ważne (w okresie obowiązywania)')
                    ->query(fn ($query) => $query->valid()),

                Tables\Filters\Filter::make('active_and_valid')
                    ->label('Aktywne i ważne')
                    ->query(fn ($query) => $query->activeAndValid()),
            ])
            ->recordActions([
                Actions\Action::make('preview')
                    ->label('Podgląd')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Promotion $record) => route('promotion.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Promotion $record) => $record->isActiveAndValid()),
                Actions\EditAction::make()
                    ->label('Edytuj'),
                Actions\DeleteAction::make()
                    ->label('Usuń'),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotions::route('/'),
            'create' => CreatePromotion::route('/create'),
            'edit' => EditPromotion::route('/{record}/edit'),
        ];
    }
}
