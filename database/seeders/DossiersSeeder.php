<?php

namespace Database\Seeders;

use App\Models\Dossier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DossiersSeeder extends Seeder
{
    public function run(): void
    {
        $directeur = User::role('directeur')->first();
        $collaborateurs = User::role('collaborateur')->get();

        $origines = [
            ['direction', 'Direction des Transports Terrestres'],
            ['direction', 'Direction des Transports Maritimes'],
            ['direction', 'Direction des Affaires Générales'],
            ['entreprise', 'Société Alpha Informatique'],
            ['entreprise', 'Entreprise Delta Logistique'],
        ];

        $priorites = ['normale', 'urgente', 'tres_urgente'];

        for ($i = 1; $i <= 10; $i++) {
            [$type, $nom] = $origines[$i % count($origines)];

            $dossier = Dossier::create([
                'reference' => Dossier::genererReference(),
                'objet' => "Demande d'appui informatique n°{$i}",
                'resume' => "Résumé du dossier d'exemple n°{$i}.",
                'origine_type' => $type,
                'origine_nom' => $nom,
                'date_reception' => now()->subDays(10 - $i),
                'priorite' => $priorites[$i % 3],
                'statut' => Dossier::STATUT_RECU,
                'echeance' => now()->addDays(7 + $i),
                'enregistre_par' => $directeur->id,
            ]);

            $dossier->journaliser($directeur, 'enregistrement', null, Dossier::STATUT_RECU, 'Dossier enregistré');

            // Les 7 derniers dossiers sont cotés à 1 ou 2 collaborateurs
            if ($i > 3 && $collaborateurs->isNotEmpty()) {
                $cotation = $dossier->cotations()->create([
                    'cotee_par' => $directeur->id,
                    'type' => 'initiale',
                    'instruction' => 'Merci de traiter ce dossier dans les délais.',
                    'echeance' => now()->addDays(7),
                ]);

                $choisis = $collaborateurs->random(min(2, $collaborateurs->count()))->values();

                foreach ($choisis as $index => $collab) {
                    $cotation->users()->attach($collab->id, [
                        'role' => $index === 0 ? 'pilote' : 'associe',
                    ]);
                }

                $dossier->update(['statut' => Dossier::STATUT_COTE]);
                $dossier->journaliser($directeur, 'cotation', Dossier::STATUT_RECU, Dossier::STATUT_COTE,
                    'Coté à : ' . $choisis->pluck('name')->implode(', '));
            }
        }
    }
}