<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa o preço de uma reserva em uma data específica.
 *
 * Essa modelagem foi criada para refletir o XML, onde uma reserva pode possuir
 * várias diárias, cada uma potencialmente vinculada a uma tarifa.
 */
class ReservationPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'rate_id',
        'reference_date',
        'amount',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Cada preço por data pertence a uma reserva.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Opcionalmente, o preço por data pode referenciar uma tarifa.
     */
    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}
