<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pago;
use App\Models\Producto;

class DetallePagoProducto extends Model
{
    use HasFactory;

    protected $table = 'detalle_pago_producto';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_pago',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
