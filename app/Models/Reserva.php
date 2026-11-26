<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reserva extends Model
{
    protected $fillable = ['cliente_id', 'fecha', 'total'];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'float',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function salidas(): BelongsToMany
    {
        return $this->belongsToMany(Salida::class, 'item_reservas')
            ->using(ItemReserva::class)
            ->withPivot('cantidad_personas', 'precio_unitario')
            ->withTimestamps();
    }

    public function agregarSalida(Salida $salida, int $cantidadPersonas): void
    {
        $this->salidas()->attach($salida->id, [
            'cantidad_personas' => $cantidadPersonas,
            'precio_unitario' => $salida->tour->precio_por_persona,
        ]);
    }

    public function calcularTotal(): float
    {
        return (float) $this->salidas()->get()->sum(
            fn (Salida $salida) => $salida->pivot->cantidad_personas * $salida->pivot->precio_unitario
        );
    }
}