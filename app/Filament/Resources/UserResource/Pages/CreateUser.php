<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Events\AdminCreatedUser;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // Only send setup email if password was not set
        if (empty($this->data['password'])) {
            // Generate password setup token
            $token = $this->record->initiatePasswordSetup();

            // Dispatch event to send email
            event(new AdminCreatedUser($this->record));
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        if (empty($this->data['password'])) {
            return 'Użytkownik utworzony - email z linkiem do ustawienia hasła wysłany';
        }

        return 'Użytkownik utworzony pomyślnie';
    }
}
