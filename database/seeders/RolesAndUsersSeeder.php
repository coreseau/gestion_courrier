<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['admin', 'directeur', 'collaborateur'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $directeur = User::firstOrCreate(
            ['email' => 'directeur@cellule.test'],
            [
                'name' => 'Directeur de la cellule',
                'password' => Hash::make('password'),
                'fonction' => 'Directeur',
                'actif' => true,
            ]
        );
        $directeur->assignRole('directeur');

        foreach ([1, 2, 3] as $i) {
            $collab = User::firstOrCreate(
                ['email' => "collaborateur{$i}@cellule.test"],
                [
                    'name' => "Collaborateur {$i}",
                    'password' => Hash::make('password'),
                    'fonction' => 'Ingénieur informaticien',
                    'actif' => true,
                ]
            );
            $collab->assignRole('collaborateur');
        }
    $admin = User::firstOrCreate(
    ['email' => 'admin@cellule.test'],
    [
        'name' => 'Administrateur',
        'password' => Hash::make('password'),
        'fonction' => 'Administrateur système',
        'actif' => true,
        'doit_changer_mot_de_passe' => true,
    ]
);
$admin->assignRole('admin');
    
    }
}