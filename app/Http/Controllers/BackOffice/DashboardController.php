<?php

namespace App\Http\Controllers\BackOffice;

use App\Enums\EtatCommande;
use App\Http\Controllers\Controller;
use App\Models\Employe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page d'accueil du back-office, affichée après la connexion d'un collaborateur.
 *
 * Elle rappelle à l'employé son rôle et les états de commande qu'il a le droit
 * d'enregistrer. Les écrans de gestion des commandes s'y rattacheront.
 *
 * Page React : resources/js/pages/backoffice/dashboard.tsx.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du collaborateur connecté.
     */
    public function __invoke(Request $request): Response
    {
        /** @var Employe $employe */
        $employe = $request->user('employe');

        return Inertia::render('backoffice/dashboard', [
            'roleLibelle' => $employe->role->libelle(),
            'etatsGerables' => array_map(
                fn (EtatCommande $etat): array => [
                    'value' => $etat->value,
                    'label' => $etat->libelle(),
                ],
                $employe->role->etatsGerables(),
            ),
        ]);
    }
}
