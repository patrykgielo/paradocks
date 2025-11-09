<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\Settings\SettingsManager;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

/**
 * System Settings Page
 *
 * Filament admin page for managing application-wide settings.
 * Settings are grouped into tabs: Booking, Map, Contact, Marketing, Email.
 */
class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * Page view.
     */
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    /**
     * Navigation group.
     */
    protected static ?string $navigationGroup = 'Settings';

    /**
     * Navigation label.
     */
    protected static ?string $navigationLabel = 'System Settings';

    /**
     * Page title.
     */
    protected static string $view = 'filament.pages.system-settings';

    /**
     * Permission required to access this page.
     */
    protected static ?string $permission = 'manage settings';

    /**
     * Form state data.
     *
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Mount the page and load settings.
     */
    public function mount(): void
    {
        $settingsManager = app(SettingsManager::class);
        $allSettings = $settingsManager->all();

        // Flatten settings for form
        $this->form->fill($allSettings);
    }

    /**
     * Define the form schema.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        $this->bookingTab(),
                        $this->mapTab(),
                        $this->contactTab(),
                        $this->marketingTab(),
                        $this->emailTab(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Booking settings tab.
     */
    private function bookingTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Booking')
            ->schema([
                Section::make('Business Hours')
                    ->description('Configure your business operating hours')
                    ->schema([
                        TextInput::make('booking.business_hours_start')
                            ->label('Business Hours Start')
                            ->type('time')
                            ->required()
                            ->helperText('Opening time (HH:MM format)'),

                        TextInput::make('booking.business_hours_end')
                            ->label('Business Hours End')
                            ->type('time')
                            ->required()
                            ->helperText('Closing time (HH:MM format)'),
                    ])
                    ->columns(2),

                Section::make('Booking Rules')
                    ->description('Configure booking and cancellation policies')
                    ->schema([
                        TextInput::make('booking.slot_interval_minutes')
                            ->label('Slot Interval (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(15)
                            ->maxValue(120)
                            ->helperText('Time interval between available slots'),

                        TextInput::make('booking.advance_booking_hours')
                            ->label('Advance Booking (hours)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Minimum hours in advance for booking'),

                        TextInput::make('booking.cancellation_hours')
                            ->label('Cancellation Window (hours)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Hours before appointment for free cancellation'),

                        TextInput::make('booking.max_service_duration_minutes')
                            ->label('Max Service Duration (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(60)
                            ->helperText('Maximum duration for a single service'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Map settings tab.
     */
    private function mapTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Map')
            ->schema([
                Section::make('Google Maps Configuration')
                    ->description('Configure Google Maps integration')
                    ->schema([
                        TextInput::make('map.default_latitude')
                            ->label('Default Latitude')
                            ->numeric()
                            ->required()
                            ->helperText('Default map center latitude'),

                        TextInput::make('map.default_longitude')
                            ->label('Default Longitude')
                            ->numeric()
                            ->required()
                            ->helperText('Default map center longitude'),

                        TextInput::make('map.default_zoom')
                            ->label('Default Zoom Level')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(20)
                            ->helperText('Default map zoom (1-20)'),

                        TextInput::make('map.country_code')
                            ->label('Country Code')
                            ->maxLength(2)
                            ->required()
                            ->helperText('Two-letter country code (e.g., "pl")'),

                        TextInput::make('map.map_id')
                            ->label('Map ID')
                            ->maxLength(255)
                            ->helperText('Google Cloud Map ID (optional)'),

                        Toggle::make('map.debug_panel_enabled')
                            ->label('Debug Panel Enabled')
                            ->helperText('Show debug panel in booking wizard'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Contact settings tab.
     */
    private function contactTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Contact')
            ->schema([
                Section::make('Business Contact Information')
                    ->description('Your business contact details')
                    ->schema([
                        TextInput::make('contact.email')
                            ->label('Contact Email')
                            ->email()
                            ->required()
                            ->helperText('Public contact email'),

                        TextInput::make('contact.phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->helperText('Contact phone number'),

                        TextInput::make('contact.address_line')
                            ->label('Address Line')
                            ->required()
                            ->helperText('Street address'),

                        TextInput::make('contact.city')
                            ->label('City')
                            ->required(),

                        TextInput::make('contact.postal_code')
                            ->label('Postal Code')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Marketing settings tab.
     */
    private function marketingTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Marketing')
            ->schema([
                Section::make('Hero Section')
                    ->description('Homepage hero section content')
                    ->schema([
                        TextInput::make('marketing.hero_title')
                            ->label('Hero Title')
                            ->required()
                            ->maxLength(100),

                        Textarea::make('marketing.hero_subtitle')
                            ->label('Hero Subtitle')
                            ->required()
                            ->maxLength(200)
                            ->rows(2),
                    ]),

                Section::make('Services Section')
                    ->description('Services section headings')
                    ->schema([
                        TextInput::make('marketing.services_heading')
                            ->label('Services Heading')
                            ->required(),

                        TextInput::make('marketing.services_subheading')
                            ->label('Services Subheading')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Features Section')
                    ->description('Features section content')
                    ->schema([
                        TextInput::make('marketing.features_heading')
                            ->label('Features Heading')
                            ->required(),

                        TextInput::make('marketing.features_subheading')
                            ->label('Features Subheading')
                            ->required(),

                        Repeater::make('marketing.features')
                            ->label('Features')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Feature Title')
                                    ->required(),

                                Textarea::make('description')
                                    ->label('Feature Description')
                                    ->required()
                                    ->rows(2),
                            ])
                            ->columns(2)
                            ->defaultItems(3)
                            ->addActionLabel('Add Feature')
                            ->collapsible(),
                    ]),

                Section::make('Call to Action')
                    ->description('CTA section content')
                    ->schema([
                        TextInput::make('marketing.cta_heading')
                            ->label('CTA Heading')
                            ->required(),

                        TextInput::make('marketing.cta_subheading')
                            ->label('CTA Subheading')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Important Information')
                    ->description('Important info section')
                    ->schema([
                        TextInput::make('marketing.important_info_heading')
                            ->label('Important Info Heading')
                            ->required(),

                        Repeater::make('marketing.important_info_points')
                            ->label('Info Points')
                            ->simple(
                                TextInput::make('point')
                                    ->label('Info Point')
                                    ->required()
                            )
                            ->defaultItems(3)
                            ->addActionLabel('Add Point'),
                    ]),
            ]);
    }

    /**
     * Email settings tab.
     */
    private function emailTab(): Tabs\Tab
    {
        return Tabs\Tab::make('Email')
            ->schema([
                Section::make('SMTP Configuration')
                    ->description('Configure SMTP server for sending emails')
                    ->schema([
                        TextInput::make('email.smtp_host')
                            ->label('SMTP Host')
                            ->required()
                            ->helperText('SMTP server hostname (e.g., smtp.gmail.com)'),

                        TextInput::make('email.smtp_port')
                            ->label('SMTP Port')
                            ->numeric()
                            ->required()
                            ->helperText('SMTP port (587 for TLS, 465 for SSL)'),

                        Select::make('email.smtp_encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ])
                            ->required()
                            ->helperText('Encryption protocol'),

                        TextInput::make('email.smtp_username')
                            ->label('SMTP Username')
                            ->helperText('SMTP authentication username (usually email)'),

                        TextInput::make('email.smtp_password')
                            ->label('SMTP Password')
                            ->password()
                            ->revealable()
                            ->helperText('SMTP authentication password'),
                    ])
                    ->columns(2),

                Section::make('Email Settings')
                    ->description('Configure sender information and retry behavior')
                    ->schema([
                        TextInput::make('email.from_name')
                            ->label('From Name')
                            ->required()
                            ->helperText('Display name for outgoing emails'),

                        TextInput::make('email.from_address')
                            ->label('From Address')
                            ->email()
                            ->required()
                            ->helperText('Email address for outgoing emails'),

                        TextInput::make('email.retry_attempts')
                            ->label('Retry Attempts')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(5)
                            ->helperText('Number of retry attempts for failed emails'),

                        TextInput::make('email.backoff_seconds')
                            ->label('Backoff Seconds')
                            ->numeric()
                            ->required()
                            ->minValue(30)
                            ->helperText('Seconds to wait between retry attempts'),
                    ])
                    ->columns(2),

                Section::make('Notification Settings')
                    ->description('Enable or disable specific email notifications')
                    ->schema([
                        Toggle::make('email.reminder_24h_enabled')
                            ->label('24-Hour Reminder Enabled')
                            ->helperText('Send reminder 24 hours before appointment'),

                        Toggle::make('email.reminder_2h_enabled')
                            ->label('2-Hour Reminder Enabled')
                            ->helperText('Send reminder 2 hours before appointment'),

                        Toggle::make('email.followup_enabled')
                            ->label('Follow-up Enabled')
                            ->helperText('Send follow-up email after appointment'),

                        Toggle::make('email.admin_digest_enabled')
                            ->label('Admin Digest Enabled')
                            ->helperText('Send daily digest to admins'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Submit form and save settings.
     */
    public function submit(): void
    {
        $data = $this->form->getState();

        $settingsManager = app(SettingsManager::class);
        $settingsManager->updateGroups($data);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    /**
     * Test email connection.
     */
    public function testEmailConnection(): void
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->email) {
                Notification::make()
                    ->title('No user email found')
                    ->danger()
                    ->send();
                return;
            }

            // Send test email
            Mail::raw('This is a test email from Paradocks system settings.', function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Test Email - Paradocks');
            });

            Notification::make()
                ->title('Test email sent successfully')
                ->body("Check your inbox at {$user->email}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Email test failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Get form actions.
     */
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Settings')
                ->submit('submit')
                ->keyBindings(['mod+s']),

            \Filament\Actions\Action::make('testEmail')
                ->label('Test Email Connection')
                ->color('gray')
                ->action('testEmailConnection')
                ->requiresConfirmation()
                ->modalHeading('Test Email Connection')
                ->modalDescription('This will send a test email to your account. Continue?')
                ->modalSubmitActionLabel('Send Test Email'),
        ];
    }
}
