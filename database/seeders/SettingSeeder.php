<?php

namespace Database\Seeders;

use App\Support\Settings\SettingsManager;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var SettingsManager $settings */
        $settings = app(SettingsManager::class);

        $settings->updateGroups([
            'booking' => $settings->bookingConfiguration(),
            'map' => $settings->mapConfiguration(),
            'contact' => $settings->contactInformation(),
            'marketing' => $settings->marketingContent(),
        ]);
    }
}
