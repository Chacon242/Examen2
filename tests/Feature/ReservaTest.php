<?php

use App\Models\Cliente;
use App\Models\Guia;
use App\Models\Reserva;
use App\Models\Salida;
use App\Models\Tour;

beforeEach(function () {
    $this->cliente = Cliente::create(['nombre' => 'Abril Chacón', 'correo' => 'abril@ucr.ac.cr']);
    $guia = Guia::create(['nombre' => 'Jorge Obando', 'telefono' => '8765-4321']);

    $pailas = Tour::create(['nombre' => 'Sendero Las Pailas', 'precio_por_persona' => 15000, 'duracion_horas' => 3]);
    $canopy = Tour::create(['nombre' => 'Canopy Rincón', 'precio_por_persona' => 35000, 'duracion_horas' => 2]);

    $this->salidaPailas = Salida::create([
        'tour_id' => $pailas->id,
        'guia_id' => $guia->id,
        'fecha' => '2026-12-20',
        'cupos_disponibles' => 12,
    ]);

    $this->salidaCanopy = Salida::create([
        'tour_id' => $canopy->id,
        'guia_id' => $guia->id,
        'fecha' => '2026-12-21',
        'cupos_disponibles' => 6,
    ]);
});

test('CP-01 reserva con dos salidas descuenta los cupos', function () {
    $this->postJson('/reservas', [
        'cliente_id' => $this->cliente->id,
        'items' => [
            ['salida_id' => $this->salidaPailas->id, 'cantidad_personas' => 4],
            ['salida_id' => $this->salidaCanopy->id, 'cantidad_personas' => 2],
        ],
    ])->assertStatus(201);

    $reserva = Reserva::first();

    expect($reserva->total)->toBe(130000.0)
        ->and($reserva->salidas)->toHaveCount(2)
        ->and($this->salidaPailas->fresh()->cupos_disponibles)->toBe(8)
        ->and($this->salidaCanopy->fresh()->cupos_disponibles)->toBe(4);
});

test('CP-02 sin cupos en la segunda salida no se guarda nada', function () {
    $this->salidaCanopy->update(['cupos_disponibles' => 1]);

    $this->postJson('/reservas', [
        'cliente_id' => $this->cliente->id,
        'items' => [
            ['salida_id' => $this->salidaPailas->id, 'cantidad_personas' => 4],
            ['salida_id' => $this->salidaCanopy->id, 'cantidad_personas' => 2],
        ],
    ])->assertStatus(422);

    expect(Reserva::count())->toBe(0)
        ->and($this->salidaPailas->fresh()->cupos_disponibles)->toBe(12)
        ->and($this->salidaCanopy->fresh()->cupos_disponibles)->toBe(1);

    $this->assertDatabaseCount('item_reservas', 0);
});