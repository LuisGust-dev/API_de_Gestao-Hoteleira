<?php

namespace App\Models;

use App\Models\Concerns\HasExternalPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa a raiz do agregado de reserva.
 *
 * A reserva concentra os dados principais do booking e se relaciona com:
 * - hotel
 * - quarto
 * - hóspedes
 * - preços por data
 */
class Reservation extends Model
{
    use HasExternalPrimaryKey;
    use HasFactory;

    protected $fillable = [
        'id',
        'hotel_id',
        'room_id',
        'room_reservation_id',
        'customer_first_name',
        'customer_last_name',
        'booked_at_date',
        'booked_at_time',
        'arrival_date',
        'departure_date',
        'currency_code',
        'meal_plan',
        'total_price',
    ];

    protected $casts = [
        'booked_at_date' => 'date',
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    /**
     * Cada reserva pertence a um hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Cada reserva pertence a um quarto.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Lista de hóspedes vinculados à reserva.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(ReservationGuest::class);
    }

    /**
     * Lista de preços por data vinculados à reserva.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ReservationPrice::class);
    }
}
