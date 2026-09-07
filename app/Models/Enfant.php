<?php

namespace App\Models;

use Database\Factories\EnfantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Un enfant rattaché au compte d'un client (association AVOIR).
 *
 * @property int $id Identifiant technique de l'enfant.
 * @property Carbon $date_naissance Date de naissance de l'enfant.
 * @property int $client_id Client parent auquel l'enfant est rattaché.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['date_naissance', 'client_id'])]
class Enfant extends Model
{
    /** @use HasFactory<EnfantFactory> */
    use HasFactory;

    /**
     * Les attributs à transtyper (cast).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
        ];
    }

    /**
     * Le client parent de cet enfant (association AVOIR).
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
