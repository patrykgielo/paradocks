<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Sms\SmsService;
use App\Support\Settings\SettingsManager;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

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
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    /**
     * Navigation group.
     */
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    /**
     * Navigation label.
     */
    protected static ?string $navigationLabel = 'System Settings';

    /**
     * Page view.
     */
    protected string $view = 'filament.pages.system-settings';

    /**
     * Permission required to access this page.
     */
    protected static ?string $permission = 'manage settings';

    /**
     * Restrict access to admins and super-admins only.
     * Overrides permission-based authorization for stricter control.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

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
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        $this->bookingTab(),
                        $this->mapTab(),
                        $this->contactTab(),
                        $this->marketingTab(),
                        $this->emailTab(),
                        $this->smsTab(),
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
     * SMS settings tab.
     */
    private function smsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('SMS')
            ->schema([
                Section::make('SMSAPI Configuration')
                    ->description('Configure SMSAPI.pl integration for sending SMS')
                    ->schema([
                        Toggle::make('sms.enabled')
                            ->label('SMS Enabled')
                            ->helperText('Enable or disable SMS notifications globally'),

                        Placeholder::make('api_token_info')
                            ->label('API Token')
                            ->content('⚙️ Configure SMSAPI_TOKEN in .env file for security')
                            ->helperText('API token is no longer stored in database for security reasons. Set SMSAPI_TOKEN in your .env file.'),

                        Select::make('sms.service')
                            ->label('Service')
                            ->options([
                                'pl' => 'SMSAPI.pl (Poland)',
                                'com' => 'SMSAPI.com (International)',
                            ])
                            ->required()
                            ->helperText('SMSAPI service endpoint'),

                        TextInput::make('sms.sender_name')
                            ->label('Sender Name')
                            ->maxLength(11)
                            ->required()
                            ->helperText('Max 11 characters, alphanumeric'),

                        Toggle::make('sms.test_mode')
                            ->label('Test Mode (Sandbox)')
                            ->helperText('Send SMS in test mode (no actual delivery)'),
                    ])
                    ->columns(2),

                Section::make('SMS Notification Settings')
                    ->description('Enable or disable specific SMS notifications')
                    ->schema([
                        Toggle::make('sms.send_booking_confirmation')
                            ->label('Booking Confirmation')
                            ->helperText('Send SMS when customer creates appointment'),

                        Toggle::make('sms.send_admin_confirmation')
                            ->label('Admin Confirmation')
                            ->helperText('Send SMS when admin confirms appointment'),

                        Toggle::make('sms.send_reminder_24h')
                            ->label('24-Hour Reminder')
                            ->helperText('Send reminder 24 hours before appointment'),

                        Toggle::make('sms.send_reminder_2h')
                            ->label('2-Hour Reminder')
                            ->helperText('Send reminder 2 hours before appointment'),

                        Toggle::make('sms.send_follow_up')
                            ->label('Follow-up SMS')
                            ->helperText('Send follow-up after completed appointment'),
                    ])
                    ->columns(2),

                Section::make('SMS Cost Control')
                    ->description('Spending limits and alerts to control SMS costs')
                    ->schema([
                        TextInput::make('sms.daily_limit')
                            ->label('Daily SMS Limit')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100000)
                            ->default(500)
                            ->required()
                            ->helperText('Maximum SMS messages per day (also configurable via SMS_DAILY_LIMIT in .env)'),

                        TextInput::make('sms.monthly_limit')
                            ->label('Monthly SMS Limit')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1000000)
                            ->default(10000)
                            ->required()
                            ->helperText('Maximum SMS messages per month (also configurable via SMS_MONTHLY_LIMIT in .env)'),

                        TextInput::make('sms.alert_threshold')
                            ->label('Alert Threshold (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(80)
                            ->suffix('%')
                            ->required()
                            ->helperText('Send alert email when reaching this percentage of daily/monthly limit'),

                        TextInput::make('sms.alert_email')
                            ->label('Alert Email')
                            ->email()
                            ->default('admin@example.com')
                            ->required()
                            ->helperText('Email address for cost alerts (also configurable via SMS_ALERT_EMAIL in .env)'),
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

        // Clear group caches to ensure frontend sees updated values
        foreach (array_keys($data) as $group) {
            Cache::forget("settings:{$group}");
        }

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

            if (! $user || ! $user->email) {
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
     * Test SMS connection.
     */
    public function testSmsConnection(): void
    {
        try {
            $user = auth()->user();

            if (! $user || ! $user->phone_e164) {
                Notification::make()
                    ->title('No phone number found')
                    ->body('Your user account does not have a phone number (phone_e164). Please add one to test SMS.')
                    ->danger()
                    ->send();

                return;
            }

            // Get SMS service
            $smsService = app(SmsService::class);

            // Send test SMS
            $result = $smsService->sendTestSms(
                $user->phone_e164,
                app()->getLocale()
            );

            Notification::make()
                ->title('Test SMS sent successfully')
                ->body("Check your phone at {$user->phone_e164}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('SMS test failed')
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

            \Filament\Actions\Action::make('testSms')
                ->label('Test SMS Connection')
                ->color('gray')
                ->action('testSmsConnection')
                ->requiresConfirmation()
                ->modalHeading('Test SMS Connection')
                ->modalDescription('This will send a test SMS to your phone number. Continue?')
                ->modalSubmitActionLabel('Send Test SMS'),
        ];
    }
}
