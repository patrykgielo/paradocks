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
        // Check checkbox instead of empty password
        if ($this->data['send_setup_email'] ?? false) {
            // Generate password setup token
            $token = $this->record->initiatePasswordSetup();

            // Dispatch event to send email
            event(new AdminCreatedUser($this->record));
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        if ($this->data['send_setup_email'] ?? false) {
            return 'Użytkownik utworzony - email z linkiem wysłany (ważny 30 minut)';
        }

        return 'Użytkownik utworzony pomyślnie';
    }
}
