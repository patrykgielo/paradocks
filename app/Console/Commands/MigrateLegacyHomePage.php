<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HomePage;
use App\Models\Service;
use App\Support\Settings\SettingsManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateLegacyHomePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'home:migrate-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy home.blade.php to new HomePage CMS';

    /**
     * Execute the console command.
     */
    public function handle(SettingsManager $settings): int
    {
        $this->info('Starting legacy home page migration...');

        // Get marketing settings (current hero content)
        $marketing = $settings->marketingContent();

        // Build sections array
        $sections = [];

        // 1. Hero Section (from marketing settings)
        $sections[] = [
            'type' => 'hero',
            'data' => [
                'background_type' => 'gradient',
                'title' => $marketing['hero_title'] ?? 'Profesjonalny Detailing',
                'subtitle' => $marketing['hero_subtitle'] ?? 'Rezerwuj online. Płać po usłudze. Gwarancja satysfakcji.',
                'cta_buttons' => [
                    [
                        'text' => 'Zarezerwuj Wizytę',
                        'url' => '/register',
                        'style' => 'primary',
                    ],
                    [
                        'text' => 'Dowiedz się więcej',
                        'url' => '#services',
                        'style' => 'secondary',
                    ],
                ],
                'overlay_opacity' => 50,
            ],
        ];

        // 2. Service Grid (all active services)
        $activeServiceIds = Service::active()->pluck('id')->toArray();

        $sections[] = [
            'type' => 'content_grid',
            'data' => [
                'content_type' => 'services',
                'content_items' => $activeServiceIds,
                'columns' => '3',
                'heading' => $marketing['services_heading'] ?? 'Nasze usługi',
                'subheading' => $marketing['services_subheading'] ?? 'Kompleksowa pielęgnacja Twojego auta na światowym poziomie',
                'background_color' => 'white',
            ],
        ];

        // 3. Feature List (hardcoded "Why Choose Us" section)
        $sections[] = [
            'type' => 'feature_list',
            'data' => [
                'features' => [
                    [
                        'icon' => 'sparkles',
                        'title' => 'Profesjonalne produkty',
                        'description' => 'Używamy tylko sprawdzonych, premium produktów od światowych marek',
                    ],
                    [
                        'icon' => 'shield-check',
                        'title' => 'Gwarancja jakości',
                        'description' => '100% satysfakcji gwarantowane. Jeśli nie jesteś zadowolony, poprawimy za darmo',
                    ],
                    [
                        'icon' => 'clock',
                        'title' => 'Rezerwacja online',
                        'description' => 'Zarezerwuj termin w 60 sekund. Bez telefonów, bez czekania',
                    ],
                    [
                        'icon' => 'user-group',
                        'title' => 'Doświadczony zespół',
                        'description' => 'Nasi detailerzy mają wieloletnie doświadczenie w pielęgnacji aut premium',
                    ],
                ],
                'layout' => 'grid',
                'columns' => '2',
                'heading' => 'Dlaczego Paradocks?',
                'subheading' => '',
                'background_color' => 'neutral-50',
            ],
        ];

        // 4. CTA Banner (final CTA section)
        $sections[] = [
            'type' => 'cta_banner',
            'data' => [
                'heading' => 'Gotowy na perfekcyjne auto?',
                'subheading' => 'Zarezerwuj termin online i doświadcz profesjonalnego detailingu już dziś',
                'background_color' => '#0891b2',
                'cta_buttons' => [
                    [
                        'text' => 'Zarezerwuj termin',
                        'url' => '/booking/step/1',
                        'style' => 'primary',
                    ],
                    [
                        'text' => 'Zobacz usługi',
                        'url' => '#services',
                        'style' => 'secondary',
                    ],
                ],
                'background_orbs' => true,
            ],
        ];

        // Save to database
        $homePage = HomePage::getInstance();
        $homePage->sections = $sections;
        $homePage->seo_title = 'Paradocks - Profesjonalny Detailing Samochodowy';
        $homePage->seo_description = 'Zarezerwuj profesjonalny detailing samochodowy online. Sprawdzone produkty, gwarancja jakości, doświadczony zespół.';
        $homePage->save();

        $this->info('✅ Migrated '.count($sections).' sections to HomePage CMS');

        // Backup old template
        $oldTemplate = resource_path('views/home.blade.php');
        $backupTemplate = resource_path('views/home-legacy.blade.php');

        if (File::exists($oldTemplate)) {
            File::copy($oldTemplate, $backupTemplate);
            $this->info('✅ Backed up old template to home-legacy.blade.php');
        }

        $this->newLine();
        $this->info('🎉 Migration completed successfully!');
        $this->info('Next steps:');
        $this->info('1. Review migrated content at /admin/home-page');
        $this->info('2. Visit / to see new dynamic home page');
        $this->info('3. Delete home.blade.php after verification (backup in home-legacy.blade.php)');

        return self::SUCCESS;
    }
}
