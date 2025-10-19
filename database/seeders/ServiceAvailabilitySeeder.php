<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get staff users
        $staffUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        })->get();

        if ($staffUsers->isEmpty()) {
            $this->command->warn('No staff users found. Please create staff users first.');
            return;
        }

        // Get all services
        $services = Service::all();

        if ($services->isEmpty()) {
            $this->command->warn('No services found. Please create services first.');
            return;
        }

        $this->command->info('Creating service availabilities...');

        // For each staff member
        foreach ($staffUsers as $staff) {
            // For each service
            foreach ($services as $service) {
                // Monday to Friday (1-5), 9:00 - 17:00
                for ($day = 1; $day <= 5; $day++) {
                    ServiceAvailability::create([
                        'service_id' => $service->id,
                        'user_id' => $staff->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                    ]);
                }

                $this->command->info("Created availability for {$staff->name} - {$service->name}");
            }
        }

        $this->command->info('Service availabilities created successfully!');
    }
}
