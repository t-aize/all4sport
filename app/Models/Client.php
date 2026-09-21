<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Un client du site marchand ou des magasins ALL4SPORT. Il peut avoir des
 * enfants rattachés à son compte (association AVOIR), pratiquer un ou plusieurs
 * sports (association PRATIQUER) et passer des commandes (association CHOISIR).
 *
 * `#[Fillable]` liste les attributs qui peuvent être renseignés en masse
 * (`Model::create`, `fill`) : cela protège contre l'ajout de champs non prévus
 * par un formulaire.
 * `#[Hidden]` exclut le mot de passe et le jeton de session des conversions en
 * tableau/JSON : ils ne sont donc jamais envoyés au navigateur.
 *
 * @property int $id Identifiant technique du client.
 * @property string $nom Nom de famille du client.
 * @property string $prenom Prénom du client.
 * @property string $email Adresse email du client (identifiant de connexion).
 * @property string|null $telephone Numéro de téléphone du client.
 * @property string $adresse Adresse postale du client (numéro et rue).
 * @property string $code_postal Code postal du client.
 * @property string $ville Ville du client.
 * @property Carbon|null $date_naissance Date de naissance du client.
 * @property Carbon|null $date_dernier_achat Date de la dernière commande passée par le client.
 * @property string $password Mot de passe (haché).
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nom', 'prenom', 'email', 'telephone', 'adresse', 'code_postal', 'ville', 'date_naissance', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Client extends Authenticatable
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, Notifiable;

    /**
     * Les attributs à transtyper (cast).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_dernier_achat' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Les enfants rattachés au compte de ce client (association AVOIR).
     *
     * @return HasMany<Enfant, $this>
     */
    public function enfants(): HasMany
    {
        return $this->hasMany(Enfant::class);
    }

    /**
     * Les sports pratiqués par ce client (association PRATIQUER).
     *
     * @return BelongsToMany<Rayon, $this>
     */
    public function rayons(): BelongsToMany
    {
        return $this->belongsToMany(Rayon::class, 'client_rayon')->withTimestamps();
    }

    /**
     * Les commandes passées par ce client (association CHOISIR).
     *
     * @return HasMany<Commande, $this>
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
