<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemReserva extends Pivot
{
    protected $table = 'item_reservas';

    public $incrementing = false;

    protected $casts = [
        'cantidad_personas' => 'integer',
        'precio_unitario' => 'float',
    ];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function salida(): BelongsTo
    {
        return $this->belongsTo(Salida::class);
    }

    public function subtotal(): float
    {
        return $this->cantidad_personas * $this->precio_unitario;
    }
}