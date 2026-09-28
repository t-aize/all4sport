<?php

namespace App\Models;

use Database\Factories\ProduitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Un produit vendu par ALL4SPORT. Sa clé primaire est sa référence métier
 * (3 lettres fournisseur + 3 lettres rayon + 12 chiffres), et non un identifiant
 * technique auto-incrémenté.
 *
 * `#[Fillable]` liste les attributs qui peuvent être renseignés en masse
 * (`Model::create`, `fill`) : cela protège contre l'ajout de champs non prévus
 * par un formulaire.
 *
 * @property string|null $reference_produit Référence unique du produit (clé primaire).
 * @property string $description_produit Description commerciale du produit.
 * @property string $cout_unitaire_ht Coût d'achat unitaire hors taxe du produit.
 * @property string $prix_vente Prix de vente unitaire TTC du produit.
 * @property int $quantite_stock_internet Quantité du produit disponible à la vente en ligne.
 * @property string|null $lieu_stockage Emplacement de stockage en entrepôt (ex : M2D3.5).
 * @property int|null $fournisseur_id Fournisseur qui livre ce produit.
 * @property int|null $rayon_id Rayon auquel appartient ce produit.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['reference_produit', 'description_produit', 'cout_unitaire_ht', 'prix_vente', 'quantite_stock_internet', 'lieu_stockage', 'fournisseur_id', 'rayon_id'])]
class Produit extends Model
{
    /** @use HasFactory<ProduitFactory> */
    use HasFactory;

    /**
     * Le nom de la clé primaire du modèle.
     *
     * @var string
     */
    protected $primaryKey = 'reference_produit';

    /**
     * Indique si l'identifiant est auto-incrémenté (non, c'est une clé métier).
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Le type de la clé primaire.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Les attributs à transtyper (cast).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cout_unitaire_ht' => 'decimal:2',
            'prix_vente' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Produit $produit): void {
            if (blank($produit->reference_produit)
                && is_int($produit->fournisseur_id)
                && is_int($produit->rayon_id)
                && Fournisseur::query()->whereKey($produit->fournisseur_id)->exists()
                && Rayon::query()->whereKey($produit->rayon_id)->exists()) {
                $produit->reference_produit = static::genererReference(
                    $produit->fournisseur_id,
                    $produit->rayon_id,
                );
            }
        });
    }

    public static function genererReference(int $fournisseurId, int $rayonId): string
    {
        $codeFournisseur = Fournisseur::query()->findOrFail($fournisseurId)->code_fournisseur;
        $codeRayon = Rayon::query()->findOrFail($rayonId)->code_rayon;
        $prefixe = strtoupper($codeFournisseur.$codeRayon);

        do {
            $suffixe = str_pad((string) random_int(0, 999_999_999_999), 12, '0', STR_PAD_LEFT);
            $reference = $prefixe.$suffixe;
        } while (static::query()->whereKey($reference)->exists());

        return $reference;
    }

    /**
     * Le fournisseur qui livre ce produit (association FOURNIR).
     *
     * @return BelongsTo<Fournisseur, $this>
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**
     * Le rayon auquel appartient ce produit (association APPARTENIR).
     *
     * @return BelongsTo<Rayon, $this>
     */
    public function rayon(): BelongsTo
    {
        return $this->belongsTo(Rayon::class);
    }

    /**
     * Les photos illustrant ce produit (association ILLUSTRER).
     *
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'reference_produit', 'reference_produit');
    }

    /**
     * Les magasins qui stockent ce produit, avec la quantité en stock
     * (association STOCKER, table pivot magasin_produit).
     *
     * @return BelongsToMany<Magasin, $this>
     */
    public function magasins(): BelongsToMany
    {
        return $this->belongsToMany(Magasin::class, 'magasin_produit', 'reference_produit', 'magasin_id', 'reference_produit', 'id')
            ->withPivot('quantite_stock_magasin')
            ->withTimestamps();
    }

    /**
     * Les commandes qui contiennent ce produit, avec la quantité et le prix
     * unitaire au moment de la commande (association CONTENIR, table pivot
     * commande_produit).
     *
     * @return BelongsToMany<Commande, $this>
     */
    public function commandes(): BelongsToMany
    {
        return $this->belongsToMany(Commande::class, 'commande_produit', 'reference_produit', 'commande_id', 'reference_produit', 'id')
            ->withPivot(['quantite_commandee', 'prix_unitaire_vente'])
            ->withTimestamps();
    }
}
