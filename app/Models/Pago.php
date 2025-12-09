<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reserva;
use App\Models\DetallePagoProducto;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pago';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_reserva',
        'monto_total',
        'monto_servicio',
        'monto_productos',
        'descuento',
        'fecha_pago',
        'notas',
        'metodo_pago',
        'tipo_pago',
        'estado',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva', 'id_reserva');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePagoProducto::class, 'id_pago', 'id_pago');
    }
}
