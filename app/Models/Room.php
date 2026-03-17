<?php

namespace App\Models;

use App\Models\Concerns\HasExternalPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa um quarto vinculado a um hotel.
 *
 * Neste projeto o quarto é a unidade usada pela regra de disponibilidade:
 * reservas conflitantes para o mesmo room_id não são permitidas.
 */
class Room extends Model
{
    use HasExternalPrimaryKey;
    use HasFactory;

    protected $fillable = [
        'id',
        'hotel_id',
        'name',
        'inventory_count',
    ];

    /**
     * Cada quarto pertence a um único hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Um quarto pode possuir várias reservas ao longo do tempo.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
