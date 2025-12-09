<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Models\Barbero;
use App\Models\Servicio;
use App\Models\Pago;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reserva';
    protected $primaryKey = 'id_reserva';

    protected $fillable = [
        'id_cliente',
        'id_barbero',
        'id_servicio',
        'estado',
        'fecha_reserva',
        'hora_inicio',
        'hora_fin',
        'monto_anticipo',
        'total',
        'notas',
    ];

    protected $casts = [
        'fecha_reserva' => 'string',
        'hora_inicio' => 'string',
        'hora_fin' => 'string',
        'monto_anticipo' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the cliente that owns the reserva.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Get the barbero that owns the reserva.
     */
    public function barbero()
    {
        return $this->belongsTo(Barbero::class, 'id_barbero', 'id_barbero');
    }

    /**
     * Get the servicio that owns the reserva.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    /**
     * Get the pagos for the reserva.
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_reserva', 'id_reserva');
    }
}
