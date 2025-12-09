<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;
use App\Models\Servicio;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'producto';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'id_categoria',
        'codigo',
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'unidad_medida',
        'estado',
        'imagenurl',
    ];

    /**
     * Get the categoria that owns the producto.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    /**
     * The servicios that belong to the producto.
     */
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_producto', 'id_producto', 'id_servicio')
            ->withPivot('cantidad')
            ->withTimestamps();
    }
}
