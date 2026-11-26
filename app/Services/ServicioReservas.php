<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Salida;
use Illuminate\Support\Facades\DB;

class ServicioReservas
{
    public function reservar(int $clienteId, array $items): Reserva
    {
        return DB::transaction(function () use ($clienteId, $items) {
            $reserva = Reserva::create([
                'cliente_id' => $clienteId,
                'fecha' => now(),
                'total' => 0,
            ]);

            foreach ($items as $item) {
                $salida = Salida::with('tour')->lockForUpdate()->findOrFail($item['salida_id']);
                $cantidad = (int) $item['cantidad_personas'];

                $salida->descontarCupos($cantidad);
                $reserva->agregarSalida($salida, $cantidad);
            }

            $reserva->update(['total' => $reserva->calcularTotal()]);

            return $reserva->load('salidas');
        });
    }
}