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
                            ->helperText('Automatycznie generowany z tytułu. Użyj "/" dla strony głównej.')
                            ->rules([
                                fn ($record): \Closure => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    if ($value === '/') {
                                        $settingsManager = app(\App\Support\Settings\SettingsManager::class);
                                        $homepageId = $settingsManager->get('cms.homepage_page_id');

                                        if (! $record || $homepageId != $record->id) {
                                            $fail('slug="/" is reserved for homepage. Set this page as homepage in Settings → CMS first.');
                                        }
                                    }
                                },
                            ]),

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

                                Forms\Components\Builder\Block::make('hero')
                                    ->label('Hero Section')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Forms\Components\Select::make('background_type')
                                            ->label('Typ tła')
                                            ->options([
                                                'gradient' => 'Gradient',
                                                'solid' => 'Kolor',
                                                'image' => 'Obraz',
                                            ])
                                            ->default('gradient')
                                            ->required()
                                            ->live(),

                                        Forms\Components\FileUpload::make('background_image')
                                            ->label('Obraz tła')
                                            ->image()
                                            ->directory('pages/hero')
                                            ->maxSize(5120)
                                            ->visible(fn ($get) => $get('background_type') === 'image'),

                                        Forms\Components\ColorPicker::make('background_color')
                                            ->label('Kolor tła')
                                            ->visible(fn ($get) => $get('background_type') === 'solid'),

                                        Forms\Components\TextInput::make('title')
                                            ->label('Tytuł')
                                            ->required()
                                            ->maxLength(100),

                                        Forms\Components\Textarea::make('subtitle')
                                            ->label('Podtytuł')
                                            ->maxLength(200)
                                            ->rows(2),

                                        Forms\Components\Repeater::make('cta_buttons')
                                            ->label('Przyciski CTA')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('Tekst')
                                                    ->required()
                                                    ->maxLength(50),

                                                Forms\Components\TextInput::make('url')
                                                    ->label('URL')
                                                    ->required()
                                                    ->url(),

                                                Forms\Components\Select::make('style')
                                                    ->label('Styl')
                                                    ->options([
                                                        'primary' => 'Primary',
                                                        'secondary' => 'Secondary',
                                                        'accent' => 'Accent',
                                                    ])
                                                    ->default('primary')
                                                    ->required(),
                                            ])
                                            ->defaultItems(1)
                                            ->maxItems(3)
                                            ->collapsible(),

                                        Forms\Components\Slider::make('overlay_opacity')
                                            ->label('Przezroczystość nakładki')
                                            ->default(50)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(10),
                                    ]),

                                Forms\Components\Builder\Block::make('content_grid')
                                    ->label('Siatka treści')
                                    ->icon('heroicon-o-squares-2x2')
                                    ->schema([
                                        Forms\Components\Select::make('content_type')
                                            ->label('Typ treści')
                                            ->options([
                                                'services' => 'Usługi',
                                                'posts' => 'Posty',
                                                'promotions' => 'Promocje',
                                                'portfolio' => 'Portfolio',
                                            ])
                                            ->required()
                                            ->live(),

                                        Forms\Components\Select::make('content_items')
                                            ->label('Wybierz elementy')
                                            ->options(function ($get) {
                                                return match ($get('content_type')) {
                                                    'services' => \App\Models\Service::where('is_active', true)->pluck('name', 'id'),
                                                    'posts' => \App\Models\Post::whereNotNull('published_at')->pluck('title', 'id'),
                                                    'promotions' => \App\Models\Promotion::where('is_active', true)->pluck('title', 'id'),
                                                    'portfolio' => \App\Models\PortfolioItem::whereNotNull('published_at')->pluck('title', 'id'),
                                                    default => [],
                                                };
                                            })
                                            ->multiple()
                                            ->searchable()
                                            ->required(),

                                        Forms\Components\Select::make('columns')
                                            ->label('Kolumny')
                                            ->options([
                                                '2' => '2',
                                                '3' => '3',
                                                '4' => '4',
                                            ])
                                            ->default('3')
                                            ->required(),

                                        Forms\Components\TextInput::make('heading')
                                            ->label('Nagłówek')
                                            ->maxLength(100),

                                        Forms\Components\Textarea::make('subheading')
                                            ->label('Podtytuł')
                                            ->maxLength(200)
                                            ->rows(2),

                                        Forms\Components\Select::make('background_color')
                                            ->label('Tło')
                                            ->options([
                                                'white' => 'Białe',
                                                'neutral-50' => 'Neutral 50',
                                                'primary-50' => 'Primary 50',
                                            ])
                                            ->default('white'),
                                    ]),

                                Forms\Components\Builder\Block::make('feature_list')
                                    ->label('Lista funkcji')
                                    ->icon('heroicon-o-star')
                                    ->schema([
                                        Forms\Components\Repeater::make('features')
                                            ->label('Funkcje')
                                            ->schema([
                                                Forms\Components\TextInput::make('icon')
                                                    ->label('Ikona Heroicon')
                                                    ->helperText('np. sparkles, shield-check, clock')
                                                    ->required(),

                                                Forms\Components\TextInput::make('title')
                                                    ->label('Tytuł')
                                                    ->required()
                                                    ->maxLength(100),

                                                Forms\Components\Textarea::make('description')
                                                    ->label('Opis')
                                                    ->required()
                                                    ->maxLength(200)
                                                    ->rows(2),
                                            ])
                                            ->defaultItems(3)
                                            ->maxItems(8)
                                            ->collapsible(),

                                        Forms\Components\Select::make('layout')
                                            ->label('Układ')
                                            ->options([
                                                'grid' => 'Siatka',
                                                'split' => 'Podzielony (z obrazem)',
                                            ])
                                            ->default('grid')
                                            ->required()
                                            ->live(),

                                        Forms\Components\Select::make('columns')
                                            ->label('Kolumny (tylko siatka)')
                                            ->options([
                                                '2' => '2',
                                                '3' => '3',
                                                '4' => '4',
                                            ])
                                            ->default('3')
                                            ->visible(fn ($get) => $get('layout') === 'grid'),

                                        Forms\Components\FileUpload::make('image')
                                            ->label('Obraz (tylko podzielony)')
                                            ->image()
                                            ->directory('pages/features')
                                            ->visible(fn ($get) => $get('layout') === 'split'),

                                        Forms\Components\TextInput::make('heading')
                                            ->label('Nagłówek')
                                            ->maxLength(100),

                                        Forms\Components\Textarea::make('subheading')
                                            ->label('Podtytuł')
                                            ->maxLength(200)
                                            ->rows(2),

                                        Forms\Components\Select::make('background_color')
                                            ->label('Tło')
                                            ->options([
                                                'white' => 'Białe',
                                                'neutral-50' => 'Neutral 50',
                                            ])
                                            ->default('white'),
                                    ]),

                                Forms\Components\Builder\Block::make('cta_banner')
                                    ->label('CTA Banner')
                                    ->icon('heroicon-o-cursor-arrow-ripple')
                                    ->schema([
                                        Forms\Components\TextInput::make('heading')
                                            ->label('Nagłówek')
                                            ->required()
                                            ->maxLength(100),

                                        Forms\Components\Textarea::make('subheading')
                                            ->label('Podtytuł')
                                            ->maxLength(200)
                                            ->rows(2),

                                        Forms\Components\ColorPicker::make('background_color')
                                            ->label('Kolor tła')
                                            ->default('#0891b2'),

                                        Forms\Components\Repeater::make('cta_buttons')
                                            ->label('Przyciski CTA')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('Tekst')
                                                    ->required()
                                                    ->maxLength(50),

                                                Forms\Components\TextInput::make('url')
                                                    ->label('URL')
                                                    ->required()
                                                    ->url(),

                                                Forms\Components\Select::make('style')
                                                    ->label('Styl')
                                                    ->options([
                                                        'primary' => 'Primary',
                                                        'secondary' => 'Secondary',
                                                    ])
                                                    ->default('primary')
                                                    ->required(),
                                            ])
                                            ->defaultItems(1)
                                            ->maxItems(2)
                                            ->collapsible(),

                                        Forms\Components\Toggle::make('background_orbs')
                                            ->label('Animowane tło')
                                            ->default(true),
                                    ]),

                                Forms\Components\Builder\Block::make('text_block')
                                    ->label('Blok tekstowy')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Forms\Components\RichEditor::make('content')
                                            ->label('Treść')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'link', 'bulletList',
                                                'orderedList', 'h2', 'h3', 'blockquote',
                                            ])
                                            ->extraInputAttributes(['style' => 'min-height: 20rem;']),

                                        Forms\Components\Select::make('layout')
                                            ->label('Układ')
                                            ->options([
                                                'default' => 'Domyślny',
                                                'full-width' => 'Pełna szerokość',
                                                'narrow' => 'Wąski',
                                            ])
                                            ->default('default')
                                            ->required(),

                                        Forms\Components\Select::make('background_color')
                                            ->label('Tło')
                                            ->options([
                                                'white' => 'Białe',
                                                'neutral-50' => 'Neutral 50',
                                                'primary-50' => 'Primary 50',
                                            ])
                                            ->default('white'),
                                    ]),

                                Forms\Components\Builder\Block::make('custom_html')
                                    ->label('Własny HTML')
                                    ->icon('heroicon-o-code-bracket')
                                    ->visible(fn () => auth()->user()?->hasRole('super-admin') ?? false)
                                    ->schema([
                                        Forms\Components\Textarea::make('html')
                                            ->label('Kod HTML')
                                            ->required()
                                            ->rows(10)
                                            ->helperText('⚠️ Używaj ostrożnie. Tylko zaufany kod HTML.'),

                                        Forms\Components\Toggle::make('container_wrapper')
                                            ->label('Opakowanie kontenera')
                                            ->default(true)
                                            ->helperText('Dodaje kontener max-width wokół HTML'),
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

                Tables\Columns\IconColumn::make('is_homepage')
                    ->label('Homepage')
                    ->boolean()
                    ->getStateUsing(function (Page $record): bool {
                        $settingsManager = app(\App\Support\Settings\SettingsManager::class);
                        $homepageId = $settingsManager->get('cms.homepage_page_id');

                        return $homepageId == $record->id;
                    })
                    ->trueIcon('heroicon-o-home')
                    ->falseIcon('')
                    ->alignCenter()
                    ->tooltip(fn (bool $state): string => $state ? 'This is the homepage' : ''),

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
                    ->url(function (Page $record) {
                        $settingsManager = app(\App\Support\Settings\SettingsManager::class);
                        $homepageId = $settingsManager->get('cms.homepage_page_id');

                        if ($homepageId == $record->id) {
                            return route('home');
                        }

                        return route('page.show', $record->slug);
                    })
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

    /**
     * Restrict access to admins and super-admins only.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }
}
