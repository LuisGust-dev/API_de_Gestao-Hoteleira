<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa a composição de hóspedes de uma reserva.
 *
 * O XML original pode trazer múltiplos guest_count, então essa estrutura foi
 * modelada como coleção separada da tabela principal de reservas.
 */
class ReservationGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'type',
        'count',
    ];

    /**
     * Cada registro de hóspede pertence a uma reserva.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
