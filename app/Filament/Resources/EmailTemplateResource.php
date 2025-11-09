<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use App\Services\Email\EmailService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Email';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Email Templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Details')
                    ->schema([
                        Forms\Components\Select::make('key')
                            ->label('Template Key')
                            ->required()
                            ->options([
                                'user-registered' => 'User Registered',
                                'password-reset' => 'Password Reset',
                                'appointment-created' => 'Appointment Created',
                                'appointment-rescheduled' => 'Appointment Rescheduled',
                                'appointment-cancelled' => 'Appointment Cancelled',
                                'appointment-reminder-24h' => 'Appointment Reminder (24h)',
                                'appointment-reminder-2h' => 'Appointment Reminder (2h)',
                                'appointment-followup' => 'Appointment Follow-up',
                                'admin-daily-digest' => 'Admin Daily Digest',
                            ])
                            ->searchable()
                            ->helperText('Unique identifier for this template'),

                        Forms\Components\Select::make('language')
                            ->label('Language')
                            ->required()
                            ->options([
                                'pl' => 'Polski (PL)',
                                'en' => 'English (EN)',
                            ])
                            ->default('pl')
                            ->helperText('Template language'),

                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable/disable this template'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Email Content')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject Line')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Welcome to {{app_name}}, {{user_name}}!')
                            ->helperText('Use {{variable}} syntax for placeholders'),

                        Forms\Components\Textarea::make('html_body')
                            ->label('HTML Body')
                            ->required()
                            ->rows(15)
                            ->placeholder('<h1>Hello {{user_name}}</h1>')
                            ->helperText('HTML template with {{variable}} placeholders. Supports Blade syntax.'),

                        Forms\Components\Textarea::make('text_body')
                            ->label('Plain Text Body (Optional)')
                            ->rows(10)
                            ->placeholder('Hello {{user_name}}...')
                            ->helperText('Plain text version for email clients that don\'t support HTML'),
                    ]),

                Forms\Components\Section::make('Available Variables')
                    ->schema([
                        Forms\Components\Placeholder::make('variable_legend')
                            ->label('')
                            ->content(fn (Get $get): HtmlString => self::getVariableLegendForKey($get('key')))
                            ->helperText('Copy these variable names into your template using {{variable_name}} syntax'),
                    ])
                    ->description('Variables you can use in the subject, HTML body, and text body')
                    ->collapsible(),

                Forms\Components\Section::make('Advanced Settings')
                    ->schema([
                        Forms\Components\TextInput::make('blade_path')
                            ->label('Blade Path (Fallback)')
                            ->placeholder('emails.user-registered')
                            ->helperText('Fallback Blade view path if database template fails'),

                        Forms\Components\TagsInput::make('variables')
                            ->label('Available Variables')
                            ->placeholder('user_name, app_name, etc.')
                            ->helperText('List of variables available for this template (for reference only)'),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('language')
                    ->label('Language')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pl' => 'success',
                        'en' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (EmailTemplate $record): string {
                        return $record->subject;
                    }),

                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('language')
                    ->label('Language')
                    ->options([
                        'pl' => 'Polski',
                        'en' => 'English',
                    ]),

                Tables\Filters\SelectFilter::make('key')
                    ->label('Template Key')
                    ->options([
                        'user-registered' => 'User Registered',
                        'password-reset' => 'Password Reset',
                        'appointment-created' => 'Appointment Created',
                        'appointment-rescheduled' => 'Appointment Rescheduled',
                        'appointment-cancelled' => 'Appointment Cancelled',
                        'appointment-reminder-24h' => 'Appointment Reminder (24h)',
                        'appointment-reminder-2h' => 'Appointment Reminder (2h)',
                        'appointment-followup' => 'Appointment Follow-up',
                        'admin-daily-digest' => 'Admin Daily Digest',
                    ]),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active Status')
                    ->placeholder('All templates')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                // Tables\Actions\Action::make('preview')
                //     ->label('Preview')
                //     ->icon('heroicon-o-eye')
                //     ->color('info')
                //     ->modalHeading(fn (EmailTemplate $record): string => "Preview: {$record->key}")
                //     ->modalContent(fn (EmailTemplate $record) => new HtmlString(
                //         view('filament.resources.email-template.preview', [
                //             'template' => $record,
                //             'rendered' => $record->render(self::getExampleData($record)),
                //             'renderedText' => $record->renderText(self::getExampleData($record)) ?? '',
                //         ])->render()
                //     ))
                //     ->modalWidth('4xl')
                //     ->modalSubmitAction(false)
                //     ->modalCancelActionLabel('Close'),

                Tables\Actions\Action::make('testSend')
                    ->label('Test Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Recipient Email')
                            ->email()
                            ->required()
                            ->placeholder('test@example.com')
                            ->helperText('Email will be sent to this address with example data'),
                    ])
                    ->action(function (EmailTemplate $record, array $data): void {
                        try {
                            $emailService = app(EmailService::class);

                            // Send test email with example data
                            $result = $emailService->sendFromTemplate(
                                templateKey: $record->key,
                                language: $record->language,
                                recipient: $data['email'],
                                data: self::getExampleData($record),
                                metadata: []
                            );

                            if ($result) {
                                Notification::make()
                                    ->success()
                                    ->title('Test email sent!')
                                    ->body("Email sent to {$data['email']}")
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('Failed to send test email')
                                    ->body('Check the email logs for details')
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error sending test email')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send Test Email')
                    ->modalDescription('This will send a test email with example data to verify the template rendering.'),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    /**
     * Get example data for template preview/testing
     */
    protected static function getExampleData(EmailTemplate $template): array
    {
        // Common variables
        $data = [
            'app_name' => config('app.name', 'Paradocks'),
            'app_url' => config('app.url', 'https://paradocks.local'),
            'user_name' => 'Jan Kowalski',
            'user_email' => 'jan.kowalski@example.com',
            'current_year' => date('Y'),
        ];

        // Template-specific variables
        $specificData = match ($template->key) {
            'user-registered' => [
                'verification_url' => url('/email/verify'),
            ],
            'password-reset' => [
                'reset_url' => url('/reset-password/token123'),
                'expires_in' => '60 minutes',
            ],
            'appointment-created', 'appointment-rescheduled', 'appointment-reminder-24h', 'appointment-reminder-2h' => [
                'appointment_date' => now()->addDays(2)->format('Y-m-d'),
                'appointment_time' => '14:00',
                'service_name' => 'Full Car Detailing',
                'location_address' => 'ul. Przykładowa 123, Warszawa',
            ],
            'appointment-cancelled' => [
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '14:00',
                'service_name' => 'Full Car Detailing',
                'cancellation_reason' => 'Customer request',
            ],
            'appointment-followup' => [
                'appointment_date' => now()->subDays(3)->format('Y-m-d'),
                'service_name' => 'Full Car Detailing',
                'feedback_url' => url('/feedback/123'),
            ],
            'admin-daily-digest' => [
                'date' => now()->format('Y-m-d'),
                'total_appointments' => 12,
                'pending_appointments' => 3,
                'completed_appointments' => 9,
            ],
            default => [],
        };

        return array_merge($data, $specificData);
    }

    /**
     * Check if user can access this resource
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage email templates') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage email templates') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage email templates') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('manage email templates') ?? false;
    }

    /**
     * Get variable legend HTML for a specific template key.
     *
     * Displays available variables that can be used in the email template,
     * including both common variables (app_name, user_name, etc.) and
     * template-specific variables (appointment_date, verification_url, etc.).
     *
     * @param string|null $key Template key (e.g., 'user-registered', 'appointment-created')
     * @return \Illuminate\Support\HtmlString HTML content showing variable list
     */
    protected static function getVariableLegendForKey(?string $key): HtmlString
    {
        if (!$key) {
            return new HtmlString('<p class="text-sm text-gray-500">Select a template key to see available variables</p>');
        }

        // Common variables available in ALL templates
        $commonVariables = [
            'app_name' => 'Application name (from config)',
            'app_url' => 'Application URL',
            'user_name' => 'User\'s full name (first_name + last_name)',
            'user_email' => 'User\'s email address',
            'customer_name' => 'Customer\'s full name (alias for user_name)',
            'current_year' => 'Current year (e.g., 2025)',
            'contact_email' => 'Support email address',
            'contact_phone' => 'Support phone number',
        ];

        // Template-specific variables
        $specificVariables = match ($key) {
            'user-registered' => [
                'verification_url' => 'Email verification link',
            ],
            'password-reset' => [
                'reset_url' => 'Password reset link',
                'expires_in' => 'Link expiration time (e.g., "60 minutes")',
            ],
            'appointment-created', 'appointment-rescheduled', 'appointment-reminder-24h', 'appointment-reminder-2h' => [
                'appointment_date' => 'Appointment date (Y-m-d format)',
                'appointment_time' => 'Appointment time (H:i format)',
                'service_name' => 'Service name',
                'location_address' => 'Service location address',
            ],
            'appointment-cancelled' => [
                'appointment_date' => 'Appointment date',
                'appointment_time' => 'Appointment time',
                'service_name' => 'Service name',
                'cancellation_reason' => 'Reason for cancellation',
            ],
            'appointment-followup' => [
                'appointment_date' => 'Appointment date',
                'service_name' => 'Service name',
                'feedback_url' => 'Feedback form link',
            ],
            'admin-daily-digest' => [
                'date' => 'Report date',
                'total_appointments' => 'Total appointments',
                'pending_appointments' => 'Pending appointments count',
                'completed_appointments' => 'Completed appointments count',
            ],
            default => [],
        };

        // Build HTML output
        $html = '<div class="space-y-4">';

        // Common variables section
        $html .= '<div>';
        $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-2">Common Variables (Available in all templates)</h4>';
        $html .= '<div class="bg-gray-50 rounded-lg p-3 space-y-1">';
        foreach ($commonVariables as $var => $description) {
            $html .= sprintf(
                '<div class="flex items-start"><code class="text-xs bg-gray-200 px-2 py-1 rounded font-mono text-blue-600 mr-2">{{%s}}</code><span class="text-xs text-gray-600">%s</span></div>',
                $var,
                $description
            );
        }
        $html .= '</div>';
        $html .= '</div>';

        // Template-specific variables section
        if (!empty($specificVariables)) {
            $html .= '<div>';
            $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-2">Template-Specific Variables</h4>';
            $html .= '<div class="bg-blue-50 rounded-lg p-3 space-y-1">';
            foreach ($specificVariables as $var => $description) {
                $html .= sprintf(
                    '<div class="flex items-start"><code class="text-xs bg-blue-200 px-2 py-1 rounded font-mono text-blue-700 mr-2">{{%s}}</code><span class="text-xs text-gray-600">%s</span></div>',
                    $var,
                    $description
                );
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }
}
