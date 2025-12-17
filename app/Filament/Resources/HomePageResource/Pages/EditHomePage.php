<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomePageResource\Pages;

use App\Filament\Resources\HomePageResource;
use App\Models\HomePage;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class EditHomePage extends EditRecord
{
    protected static string $resource = HomePageResource::class;

    /**
     * Custom heading.
     */
    public function getHeading(): string
    {
        return 'Zarządzaj stroną główną';
    }

    /**
     * Mount the page and ensure singleton record exists.
     */
    public function mount(int|string $record = 1): void
    {
        // Always load record with id=1 (singleton)
        $this->record = HomePage::getInstance();

        $this->fillForm();
    }

    /**
     * Get header actions.
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clear_cache')
                ->label('Wyczyść cache')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    Cache::forget('home.full_page');
                    Cache::forget('home.sections');

                    Notification::make()
                        ->title('Cache wyczyszczony')
                        ->body('Cache strony głównej został wyczyszczony pomyślnie.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('preview')
                ->label('Podgląd strony głównej')
                ->icon('heroicon-o-eye')
                ->url('/')
                ->openUrlInNewTab()
                ->color('gray'),

            Actions\Action::make('reset_to_default')
                ->label('Przywróć domyślne')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Przywróć domyślne ustawienia?')
                ->modalDescription('Spowoduje to przywrócenie 4 oryginalnych sekcji (Hero, Usługi, Dlaczego My, CTA). Wszystkie obecne sekcje zostaną zastąpione.')
                ->modalSubmitActionLabel('Tak, przywróć')
                ->action(function () {
                    Artisan::call('home:migrate-legacy');
                    $this->fillForm();

                    Notification::make()
                        ->title('Przywrócono domyślne ustawienia')
                        ->body('Strona główna została przywrócona do oryginalnej konfiguracji.')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Redirect after save.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit');
    }

    /**
     * Notification after save.
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Zapisano')
            ->body('Strona główna została zaktualizowana. Cache został automatycznie wyczyszczony.');
    }
}
