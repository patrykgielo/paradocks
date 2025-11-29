<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Service management
            'view services',
            'create services',
            'edit services',
            'delete services',

            // Appointment management
            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',
            'view own appointments',
            'cancel own appointments',

            // Availability management
            'manage availability',
            'view availability',

            // Email management
            'manage email templates',
            'view email logs',
            'view email events',
            'manage suppressions',

            // Settings
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - most permissions except user management
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view users',
            'view services',
            'create services',
            'edit services',
            'delete services',
            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',
            'manage availability',
            'view availability',
            'manage email templates',
            'view email logs',
            'view email events',
            'manage suppressions',
            'manage settings',
        ]);

        // Staff - can manage own availability and appointments
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'view services',
            'view appointments',
            'create appointments',
            'edit appointments',
            'manage availability',
            'view availability',
            'view email logs',
            'view email events',
        ]);

        // Customer - can only view and book appointments
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'view services',
            'view own appointments',
            'create appointments',
            'cancel own appointments',
        ]);

        // Create default admin user if doesn't exist
        $adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('super-admin');
        }
    }
}
