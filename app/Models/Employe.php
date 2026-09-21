<?php

namespace App\Models;

use App\Enums\RoleEmploye;
use Database\Factories\EmployeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Un collaborateur d'ALL4SPORT (opérateur, vendeur, livreur ou service client).
 * Il peut traiter des commandes en magasin (association PASSER) et enregistrer
 * les changements d'état d'une commande (association ENREGISTRER).
 *
 * `#[Fillable]` liste les attributs qui peuvent être renseignés en masse
 * (`Model::create`, `fill`) : cela protège contre l'ajout de champs non prévus
 * par un formulaire.
 * `#[Hidden]` exclut le mot de passe et le jeton de session des conversions en
 * tableau/JSON : ils ne sont donc jamais envoyés au navigateur.
 *
 * @property int $id Identifiant technique du collaborateur.
 * @property string $nom Nom de famille du collaborateur.
 * @property string $prenom Prénom du collaborateur.
 * @property string $identifiant_connexion Identifiant de connexion du collaborateur.
 * @property string $password Mot de passe (haché).
 * @property RoleEmploye $role Rôle du collaborateur (opérateur, vendeur, livreur, service client).
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nom', 'prenom', 'identifiant_connexion', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class Employe extends Authenticatable
{
    /** @use HasFactory<EmployeFactory> */
    use HasFactory, Notifiable;

    /**
     * Les attributs à transtyper (cast).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => RoleEmploye::class,
            'password' => 'hashed',
        ];
    }

    /**
     * Les commandes traitées par ce collaborateur (association PASSER).
     *
     * @return HasMany<Commande, $this>
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    /**
     * Les changements d'état de commande enregistrés par ce collaborateur
     * (association ENREGISTRER).
     *
     * @return HasMany<HistoriqueEtat, $this>
     */
    public function historiqueEtats(): HasMany
    {
        return $this->hasMany(HistoriqueEtat::class);
    }
}
