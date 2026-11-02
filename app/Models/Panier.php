<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class Panier extends Model
{
    protected $table = 'paniers';
 
    protected $fillable = ['client_id', 'session_id'];
 
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
 
    public function lignes(): HasMany
    {
        return $this->hasMany(LignePanier::class);
    }
 
    /** Montant total du panier : $panier->total */
    public function getTotalAttribute(): float
    {
        return (float) $this->lignes->sum(fn (LignePanier $ligne) => $ligne->sous_total);
    }
 
    /** Nombre total d'unités dans le panier : $panier->nombre_articles */
    public function getNombreArticlesAttribute(): int
    {
        return (int) $this->lignes->sum('quantite');
    }
}
 
