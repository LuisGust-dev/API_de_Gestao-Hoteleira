<?php

namespace App\Models;

use App\Models\Concerns\HasExternalPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa um hotel persistido a partir do XML de hotéis.
 *
 * Model funciona como raiz de relacionamento para quartos, tarifas e reservas.
 */
class Hotel extends Model
{
    use HasExternalPrimaryKey;
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * Um hotel pode possuir vários quartos.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Um hotel pode possuir várias tarifas.
     */
    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    /**
     * Um hotel pode possuir várias reservas.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
