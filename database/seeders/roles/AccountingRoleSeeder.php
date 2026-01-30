<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AccountingRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Pagaments
            'payments.view',
            'payments.create',
            'payments.export',

            // Professorat (només dades econòmiques)
            'teachers.view',
            'teachers.financial_data.view',
            'teachers.financial_data.update',

            // Consentiments
            'consents.request',
            'consents.view',

            // Informes
            'reports.financial',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $role = Role::firstOrCreate(['name' => 'accounting']);
        $role->syncPermissions($permissions);
    }
}
