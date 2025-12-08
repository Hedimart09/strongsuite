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

            // Settings permissions
            'settings.view',
            'settings.edit',

            // Staff permissions
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $receptionistRole = Role::create(['name' => 'Receptionist']);
        $trainerRole = Role::create(['name' => 'Trainer']);

        // Admin gets all permissions
        $adminRole->givePermissionTo(Permission::all());

        // Receptionist permissions (limited to front desk operations)
        $receptionistRole->givePermissionTo([
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
        ]);

        // Trainer permissions (view-only and attendance management)
        $trainerRole->givePermissionTo([
            'members.view',
            'attendance.view',
            'attendance.create',
            'reports.view',
        ]);
    }
}
