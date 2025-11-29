<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use Illuminate\Console\Command;

class EnsureStaffAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:ensure-availability
                            {--check : Only check which staff members have no availability}
                            {--fix : Create default availability for staff without any}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all staff members have service availability configured';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking staff availability...');
        $this->newLine();

        // Get all staff members
        $staffMembers = User::role('staff')->get();

        if ($staffMembers->isEmpty()) {
            $this->warn('No staff members found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$staffMembers->count()} staff members.");
        $this->newLine();

        // Find staff without availability
        $staffWithoutAvailability = $staffMembers->filter(function ($staff) {
            return $staff->serviceAvailabilities()->count() === 0;
        });

        if ($staffWithoutAvailability->isEmpty()) {
            $this->info('✓ All staff members have availability configured.');

            return Command::SUCCESS;
        }

        // Display staff without availability
        $this->warn("Found {$staffWithoutAvailability->count()} staff members without availability:");
        $this->newLine();

        $tableData = $staffWithoutAvailability->map(function ($staff) {
            return [
                'ID' => $staff->id,
                'Name' => $staff->name,
                'Email' => $staff->email,
                'Appointments' => $staff->staffAppointments()->count(),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Email', 'Appointments'],
            $tableData
        );

        // If --check only, stop here
        if ($this->option('check')) {
            $this->newLine();
            $this->info('Use --fix option to create default availability for these staff members.');

            return Command::SUCCESS;
        }

        // If --fix, create default availability
        if ($this->option('fix')) {
            return $this->createDefaultAvailability($staffWithoutAvailability);
        }

        // Ask user if they want to create default availability
        if ($this->confirm('Do you want to create default availability (Mon-Fri, 9:00-17:00, all services) for these staff members?', true)) {
            return $this->createDefaultAvailability($staffWithoutAvailability);
        }

        return Command::SUCCESS;
    }

    /**
     * Create default availability for given staff members
     */
    protected function createDefaultAvailability($staffMembers)
    {
        $services = Service::all();

        if ($services->isEmpty()) {
            $this->error('No services found. Please create services first.');

            return Command::FAILURE;
        }

        $this->info('Creating default availability (Monday-Friday, 9:00-17:00) for all services...');
        $this->newLine();

        $created = 0;
        $bar = $this->output->createProgressBar($staffMembers->count() * $services->count() * 5);

        foreach ($staffMembers as $staff) {
            foreach ($services as $service) {
                // Monday to Friday (1-5)
                for ($day = 1; $day <= 5; $day++) {
                    ServiceAvailability::create([
                        'user_id' => $staff->id,
                        'service_id' => $service->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                    ]);
                    $created++;
                    $bar->advance();
                }
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Successfully created {$created} availability records for {$staffMembers->count()} staff members.");
        $this->newLine();

        // Show summary
        $this->info('Summary:');
        foreach ($staffMembers as $staff) {
            $count = $staff->serviceAvailabilities()->count();
            $this->line("  - {$staff->name}: {$count} availabilities");
        }

        return Command::SUCCESS;
    }
}
