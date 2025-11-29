<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
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

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Strona';

    protected static ?string $pluralModelLabel = 'Strony';

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

                        Forms\Components\Select::make('layout')
                            ->label('Layout')
                            ->options([
                                'default' => 'Domyślny (z sidebarami)',
                                'full-width' => 'Pełna szerokość',
                                'minimal' => 'Minimalny (wąski)',
                            ])
                            ->default('default')
                            ->required(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Data publikacji')
                            ->helperText('Pozostaw puste dla wersji roboczej'),
                    ])
                    ->columns(2),

                Section::make('Główna treść')
                    ->schema([
                        Forms\Components\RichEditor::make('body')
                            ->label('Treść strony')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'link', 'bulletList', 'orderedList',
                                'h2', 'h3', 'blockquote',
                                'table',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull()
                            ->extraInputAttributes(['style' => 'min-height: 30rem;'])
                            ->helperText('Główna treść strony. Obrazki dodaj przez bloki poniżej.'),
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
                                            ->directory('pages/images')
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

                                Forms\Components\Builder\Block::make('gallery')
                                    ->label('Galeria')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Forms\Components\FileUpload::make('images')
                                            ->label('Zdjęcia')
                                            ->image()
                                            ->multiple()
                                            ->required()
                                            ->directory('pages/galleries')
                                            ->maxSize(5120)
                                            ->maxFiles(20)
                                            ->reorderable(),

                                        Forms\Components\Select::make('columns')
                                            ->label('Liczba kolumn')
                                            ->options([
                                                '2' => '2 kolumny',
                                                '3' => '3 kolumny',
                                                '4' => '4 kolumny',
                                            ])
                                            ->default('3'),
                                    ]),

                                Forms\Components\Builder\Block::make('video')
                                    ->label('Wideo')
                                    ->icon('heroicon-o-film')
                                    ->schema([
                                        Forms\Components\TextInput::make('url')
                                            ->label('URL YouTube lub Vimeo')
                                            ->url()
                                            ->required()
                                            ->helperText('np. https://www.youtube.com/watch?v=...'),

                                        Forms\Components\TextInput::make('caption')
                                            ->label('Podpis')
                                            ->maxLength(255),
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
                                            ->default('Dowiedz się więcej')
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
                                            ->default('primary'),
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
                                                'orderedList', 'h3', 'blockquote',
                                            ]),

                                        Forms\Components\RichEditor::make('right_column')
                                            ->label('Prawa kolumna')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'bulletList',
                                                'orderedList', 'h3', 'blockquote',
                                            ]),
                                    ]),

                                Forms\Components\Builder\Block::make('three_columns')
                                    ->label('Trzy kolumny')
                                    ->icon('heroicon-o-squares-2x2')
                                    ->schema([
                                        Forms\Components\RichEditor::make('column_1')
                                            ->label('Kolumna 1')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'bulletList',
                                            ]),

                                        Forms\Components\RichEditor::make('column_2')
                                            ->label('Kolumna 2')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'bulletList',
                                            ]),

                                        Forms\Components\RichEditor::make('column_3')
                                            ->label('Kolumna 3')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'bulletList',
                                            ]),
                                    ]),

                                Forms\Components\Builder\Block::make('quote')
                                    ->label('Cytat')
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->schema([
                                        Forms\Components\Textarea::make('quote')
                                            ->label('Cytat')
                                            ->required()
                                            ->rows(3)
                                            ->maxLength(500),

                                        Forms\Components\TextInput::make('author')
                                            ->label('Autor')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('author_title')
                                            ->label('Tytuł autora')
                                            ->maxLength(255)
                                            ->helperText('np. CEO, Dyrektor'),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->blockNumbers(false)
                            ->reorderable()
                            ->addActionLabel('Dodaj blok')
                            ->columnSpanFull()
                            ->helperText('Opcjonalne: dodaj galerie, wideo, przyciski CTA'),
                    ])
                    ->collapsed(),

                Section::make('SEO')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Zdjęcie wyróżniające')
                            ->image()
                            ->directory('pages/featured')
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

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('layout')
                    ->label('Layout')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'default' => 'info',
                        'full-width' => 'success',
                        'minimal' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'default' => 'Domyślny',
                        'full-width' => 'Pełna szerokość',
                        'minimal' => 'Minimalny',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Status')
                    ->badge()
                    ->dateTime('Y-m-d H:i')
                    ->color(fn ($state) => $state && $state->isPast() ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state
                            ? ($state->isPast() ? 'Opublikowano' : 'Zaplanowano')
                            : 'Wersja robocza'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ostatnia aktualizacja')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('published')
                    ->label('Opublikowane')
                    ->query(fn ($query) => $query->published()),

                Tables\Filters\Filter::make('draft')
                    ->label('Wersje robocze')
                    ->query(fn ($query) => $query->draft()),

                Tables\Filters\SelectFilter::make('layout')
                    ->label('Layout')
                    ->options([
                        'default' => 'Domyślny',
                        'full-width' => 'Pełna szerokość',
                        'minimal' => 'Minimalny',
                    ]),
            ])
            ->recordActions([
                Actions\Action::make('preview')
                    ->label('Podgląd')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record) => route('page.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Page $record) => $record->published_at && $record->published_at->isPast()),
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
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
