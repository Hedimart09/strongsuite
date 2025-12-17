<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Member permissions
            'members.view',
            'members.create',
            'members.edit',
            'members.delete',

            // Membership Plan permissions
            'membership-plans.view',
            'membership-plans.create',
            'membership-plans.edit',
            'membership-plans.delete',

            // Subscription permissions
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.edit',
            'subscriptions.delete',

            // Payment permissions
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',

            // Invoice permissions
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',

            // Attendance permissions
            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',

            // Report permissions
            'reports.view',
            'reports.export',

            // Finance permissions
            'finance.view',
            'finance.manage',

            // Additional payment permissions
            'payments.refund',

            // Settings permissions
            'settings.view',
            'settings.edit',

            // Staff permissions
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
        ];

        // Create permissions (use firstOrCreate to avoid duplicates)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions (use firstOrCreate to avoid duplicates)
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $receptionistRole = Role::firstOrCreate(['name' => 'Receptionist']);
        $trainerRole = Role::firstOrCreate(['name' => 'Trainer']);

        // Admin gets all permissions (sync to update existing role)
        $adminRole->syncPermissions(Permission::all());

        // Receptionist permissions (limited to front desk operations)
        $receptionistRole->syncPermissions([
            'members.view',
            'members.create',
            'members.edit',
            'membership-plans.view',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.edit',
            'payments.view',
            'payments.create',
            'payments.edit',
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'attendance.view',
            'attendance.create',
            'reports.view',
            'finance.view',
        ]);

        // Trainer permissions (view-only and attendance management)
        $trainerRole->syncPermissions([
            'members.view',
            'attendance.view',
            'attendance.create',
            'reports.view',
        ]);
    }
}
