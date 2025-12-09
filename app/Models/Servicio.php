<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;
use App\Models\Producto;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicio';
    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_minutos',
        'precio',
        'estado',
        'imagen',
        'id_categoria',
    ];

    /**
     * The productos that belong to the servicio.
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'servicio_producto', 'id_servicio', 'id_producto')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    /**
     * Get the reservas for the servicio.
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_servicio', 'id_servicio');
    }
}
