<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HomePageResource\Pages;
use App\Models\HomePage;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\Service;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class HomePageResource extends Resource
{
    protected static ?string $model = HomePage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'Strona główna';

    protected static ?string $pluralModelLabel = 'Strona główna';

    /**
     * Disable list page (singleton pattern).
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Sekcje strony głównej')
                    ->description('Zarządzaj sekcjami na stronie głównej. Przeciągnij sekcje, aby zmienić kolejność.')
                    ->schema([
                        Forms\Components\Builder::make('sections')
                            ->label('Sekcje')
                            ->blocks([

                                // ======== HERO SECTION ========
                                Forms\Components\Builder\Block::make('hero')
                                    ->label('Sekcja Hero (baner powitalny)')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Forms\Components\Select::make('background_type')
                                            ->label('Typ tła')
                                            ->options([
                                                'gradient' => 'Gradient (podstawowy odcień)',
                                                'solid' => 'Jednolity kolor',
                                                'image' => 'Zdjęcie',
                                            ])
                                            ->default('gradient')
                                            ->required()
                                            ->live(),

                                        Forms\Components\FileUpload::make('background_image')
                                            ->label('Zdjęcie tła')
                                            ->image()
                                            ->directory('home/hero')
                                            ->maxSize(5120)
                                            ->visible(fn ($get) => $get('background_type') === 'image'),

                                        Forms\Components\ColorPicker::make('background_color')
                                            ->label('Kolor tła')
                                            ->visible(fn ($get) => $get('background_type') === 'solid'),

                                        Forms\Components\TextInput::make('title')
                                            ->label('Tytuł')
                                            ->required()
                                            ->maxLength(100)
                                            ->helperText('Maksymalnie 100 znaków'),

                                        Forms\Components\Textarea::make('subtitle')
                                            ->label('Podtytuł')
                                            ->maxLength(200)
                                            ->rows(2)
                                            ->helperText('Opcjonalnie, maksymalnie 200 znaków'),

                                        Forms\Components\Repeater::make('cta_buttons')
                                            ->label('Przyciski CTA')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('Tekst przycisku')
                                                    ->required()
                                                    ->maxLength(50),

                                                Forms\Components\TextInput::make('url')
                                                    ->label('URL')
                                                    ->required()
                                                    ->url(),

                                                Forms\Components\Select::make('style')
                                                    ->label('Styl')
                                                    ->options([
                                                        'primary' => 'Podstawowy (biały)',
                                                        'secondary' => 'Drugorzędny (przezroczysty)',
                                                        'accent' => 'Akcentowy (pomarańczowy)',
                                                    ])
                                                    ->default('primary')
                                                    ->required(),
                                            ])
                                            ->defaultItems(2)
                                            ->maxItems(3)
                                            ->collapsible()
                                            ->addActionLabel('Dodaj przycisk'),

                                        Forms\Components\Slider::make('overlay_opacity')
                                            ->label('Przezroczystość nakładki')
                                            ->default(50)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(10)
                                            ->helperText('0 = brak nakładki, 100 = ciemna nakładka'),
                                    ]),

                                // ======== CONTENT GRID SECTION ========
                                Forms\Components\Builder\Block::make('content_grid')
                                    ->label('Siatka treści (usługi, wpisy, portfolio)')
                                    ->icon('heroicon-o-squares-2x2')
                                    ->schema([
                                        Forms\Components\Select::make('content_type')
                                            ->label('Typ treści')
                                            ->options([
                                                'services' => 'Usługi',
                                                'posts' => 'Wpisy',
                                                'promotions' => 'Promocje',
                                                'portfolio' => 'Portfolio',
                                            ])
                                            ->default('services')
                                            ->required()
                                            ->live(),

                                        Forms\Components\Select::make('content_items')
                                            ->label('Wybierz elementy')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->options(function (callable $get) {
                                                return match ($get('content_type')) {
                                                    'services' => Service::active()->pluck('name', 'id'),
                                                    'posts' => Post::published()->pluck('title', 'id'),
                                                    'promotions' => Promotion::active()->pluck('title', 'id'),
                                                    'portfolio' => PortfolioItem::published()->pluck('title', 'id'),
                                                    default => [],
                                                };
                                            })
                                            ->required()
                                            ->helperText('Wybierz elementy do wyświetlenia (zachowana zostanie kolejność)'),

                                        Forms\Components\Select::make('columns')
                                            ->label('Liczba kolumn')
                                            ->options([
                                                '2' => '2 kolumny',
                                                '3' => '3 kolumny',
                                                '4' => '4 kolumny',
                                            ])
                                            ->default('3')
                                            ->required(),

                                        Forms\Components\TextInput::make('heading')
                                            ->label('Nagłówek sekcji')
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('subheading')
                                            ->label('Podtytuł sekcji')
                                            ->maxLength(200),

                                        Forms\Components\Select::make('background_color')
                                            ->label('Kolor tła')
                                            ->options([
                                                'white' => 'Biały',
                                                'neutral-50' => 'Jasny szary',
                                                'primary-50' => 'Jasny turkusowy',
                                            ])
                                            ->default('white')
                                            ->required(),
                                    ]),

                                // ======== FEATURE LIST SECTION ========
                                Forms\Components\Builder\Block::make('feature_list')
                                    ->label('Lista funkcji / cech')
                                    ->icon('heroicon-o-list-bullet')
                                    ->schema([
                                        Forms\Components\Repeater::make('features')
                                            ->label('Funkcje')
                                            ->schema([
                                                Forms\Components\TextInput::make('icon')
                                                    ->label('Ikona (Heroicon)')
                                                    ->required()
                                                    ->default('sparkles')
                                                    ->helperText('Nazwa ikony Heroicon (np. sparkles, shield-check, clock)'),

                                                Forms\Components\TextInput::make('title')
                                                    ->label('Tytuł')
                                                    ->required()
                                                    ->maxLength(100),

                                                Forms\Components\Textarea::make('description')
                                                    ->label('Opis')
                                                    ->required()
                                                    ->maxLength(300)
                                                    ->rows(2),
                                            ])
                                            ->defaultItems(3)
                                            ->maxItems(8)
                                            ->collapsible()
                                            ->addActionLabel('Dodaj funkcję'),

                                        Forms\Components\Select::make('layout')
                                            ->label('Układ')
                                            ->options([
                                                'grid' => 'Siatka (tylko funkcje)',
                                                'split' => 'Podzielony (funkcje + zdjęcie)',
                                            ])
                                            ->default('grid')
                                            ->required()
                                            ->live(),

                                        Forms\Components\Select::make('columns')
                                            ->label('Liczba kolumn')
                                            ->options([
                                                '2' => '2 kolumny',
                                                '3' => '3 kolumny',
                                                '4' => '4 kolumny',
                                            ])
                                            ->default('2')
                                            ->required()
                                            ->visible(fn ($get) => $get('layout') === 'grid'),

                                        Forms\Components\FileUpload::make('image')
                                            ->label('Zdjęcie')
                                            ->image()
                                            ->directory('home/features')
                                            ->maxSize(5120)
                                            ->visible(fn ($get) => $get('layout') === 'split')
                                            ->helperText('Wyświetlane po prawej stronie obok listy funkcji'),

                                        Forms\Components\TextInput::make('heading')
                                            ->label('Nagłówek sekcji')
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('subheading')
                                            ->label('Podtytuł sekcji')
                                            ->maxLength(200),

                                        Forms\Components\Select::make('background_color')
                                            ->label('Kolor tła')
                                            ->options([
                                                'white' => 'Biały',
                                                'neutral-50' => 'Jasny szary',
                                            ])
                                            ->default('neutral-50')
                                            ->required(),
                                    ]),

                                // ======== CTA BANNER SECTION ========
                                Forms\Components\Builder\Block::make('cta_banner')
                                    ->label('Banner CTA (wezwanie do działania)')
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
                                            ->default('#0891b2'), // primary-600

                                        Forms\Components\Repeater::make('cta_buttons')
                                            ->label('Przyciski CTA')
                                            ->schema([
                                                Forms\Components\TextInput::make('text')
                                                    ->label('Tekst przycisku')
                                                    ->required()
                                                    ->maxLength(50),

                                                Forms\Components\TextInput::make('url')
                                                    ->label('URL')
                                                    ->required()
                                                    ->url(),

                                                Forms\Components\Select::make('style')
                                                    ->label('Styl')
                                                    ->options([
                                                        'primary' => 'Podstawowy (biały)',
                                                        'secondary' => 'Drugorzędny (przezroczysty)',
                                                    ])
                                                    ->default('primary')
                                                    ->required(),
                                            ])
                                            ->defaultItems(1)
                                            ->maxItems(2)
                                            ->collapsible()
                                            ->addActionLabel('Dodaj przycisk'),

                                        Forms\Components\Toggle::make('background_orbs')
                                            ->label('Animowane orby w tle')
                                            ->default(true)
                                            ->helperText('Włącz/wyłącz animowane gradienty w tle'),
                                    ]),

                                // ======== TEXT BLOCK SECTION ========
                                Forms\Components\Builder\Block::make('text_block')
                                    ->label('Blok tekstowy')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Forms\Components\RichEditor::make('content')
                                            ->label('Treść')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'underline', 'strike',
                                                'link', 'bulletList', 'orderedList',
                                                'h2', 'h3', 'blockquote',
                                                'table',
                                                'undo', 'redo',
                                            ])
                                            ->extraInputAttributes(['style' => 'min-height: 20rem;']),

                                        Forms\Components\Select::make('layout')
                                            ->label('Układ')
                                            ->options([
                                                'default' => 'Standardowy (kontener)',
                                                'full-width' => 'Pełna szerokość',
                                                'narrow' => 'Wąski (max 700px)',
                                            ])
                                            ->default('default')
                                            ->required(),

                                        Forms\Components\Select::make('background_color')
                                            ->label('Kolor tła')
                                            ->options([
                                                'white' => 'Biały',
                                                'neutral-50' => 'Jasny szary',
                                                'primary-50' => 'Jasny turkusowy',
                                            ])
                                            ->default('white')
                                            ->required(),
                                    ]),

                                // ======== CUSTOM HTML SECTION ========
                                Forms\Components\Builder\Block::make('custom_html')
                                    ->label('Niestandardowy HTML')
                                    ->icon('heroicon-o-code-bracket')
                                    ->schema([
                                        Forms\Components\Textarea::make('html')
                                            ->label('Kod HTML')
                                            ->required()
                                            ->rows(10)
                                            ->extraInputAttributes(['style' => 'font-family: monospace; font-size: 13px;'])
                                            ->helperText('⚠️ UWAGA: Nieprawidłowy HTML może zepsuć układ strony. Używaj ostrożnie.'),

                                        Forms\Components\Toggle::make('container_wrapper')
                                            ->label('Kontener wrapper')
                                            ->default(true)
                                            ->helperText('Włącz: HTML w kontenerze. Wyłącz: pełna szerokość.'),
                                    ])
                                    ->visible(fn () => auth()->user()?->hasRole('super-admin') ?? false),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->blockNumbers(false)
                            ->reorderable()
                            ->addActionLabel('Dodaj sekcję')
                            ->deleteAction(
                                fn (Forms\Components\Builder\Block\Actions\DeleteAction $action) => $action->label('Usuń sekcję')
                            )
                            ->columnSpanFull()
                            ->helperText('Dodaj sekcje i przeciągnij, aby zmienić kolejność wyświetlania'),
                    ]),

                Section::make('SEO')
                    ->description('Optymalizacja pod kątem wyszukiwarek')
                    ->schema([
                        Forms\Components\FileUpload::make('seo_image')
                            ->label('Zdjęcie Open Graph')
                            ->image()
                            ->directory('home/seo')
                            ->maxSize(5120)
                            ->helperText('Wyświetlane przy udostępnianiu strony w social media'),

                        Forms\Components\TextInput::make('seo_title')
                            ->label('Meta tytuł')
                            ->maxLength(60)
                            ->helperText('Zalecane: do 60 znaków. Pozostaw puste dla domyślnego.'),

                        Forms\Components\Textarea::make('seo_description')
                            ->label('Meta opis')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('Zalecane: do 160 znaków. Pozostaw puste dla domyślnego.'),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditHomePage::route('/'),
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
