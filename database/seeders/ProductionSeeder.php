<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['admin', 'directeur', 'collaborateur'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $motDePasse = Str::password(14, true, true, false);

        $admin = User::firstOrCreate(
            ['email' => 'admin@ci.cm'], // remplacez par votre courriel
            [
                'name' => 'Administrateur',
                'password' => $motDePasse,
                'fonction' => 'Administrateur système',
                'actif' => true,
                'doit_changer_mot_de_passe' => true,
            ]
        );
        $admin->assignRole('admin');

        if ($admin->wasRecentlyCreated) {
            $this->command->warn("Compte admin créé : {$admin->email} / mot de passe temporaire : {$motDePasse}");
        }
    }
}