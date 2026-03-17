<?php

namespace App\Models;

use App\Models\Concerns\HasExternalPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa uma tarifa disponível para um hotel.
 *
 * As tarifas são importadas do XML e também podem ser referenciadas pelas diárias
 * associadas a cada reserva.
 */
class Rate extends Model
{
    use HasExternalPrimaryKey;
    use HasFactory;

    protected $fillable = [
        'id',
        'hotel_id',
        'name',
        'active',
        'price',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Cada tarifa pertence a um hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
