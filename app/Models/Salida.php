<?php

namespace App\Models;

use App\Exceptions\CuposInsuficientesException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salida extends Model
{
    protected $table = 'salidas';

    protected $fillable = ['tour_id', 'guia_id', 'fecha', 'cupos_disponibles'];

    protected $casts = [
        'fecha' => 'date',
        'cupos_disponibles' => 'integer',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function guia(): BelongsTo
    {
        return $this->belongsTo(Guia::class);
    }

    public function descontarCupos(int $cantidad): void
    {
        $actualizadas = static::query()
            ->whereKey($this->getKey())
            ->where('cupos_disponibles', '>=', $cantidad)
            ->decrement('cupos_disponibles', $cantidad);

        if ($actualizadas === 0) {
            throw new CuposInsuficientesException("No hay cupos suficientes para la salida {$this->id} ");
        }

        $this->refresh();
    }
}