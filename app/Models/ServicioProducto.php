<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioProducto extends Model
{
    use HasFactory;

    protected $table = 'servicio_producto';
    
    // Disable auto-incrementing as we have a composite primary key
    public $incrementing = false;
    
    // No need for $primaryKey as we'll define a composite key in the model
    protected $primaryKey = ['id_servicio', 'id_producto'];
    
    protected $fillable = [
        'id_servicio',
        'id_producto',
        'cantidad',
    ];
    
    // Required for composite keys
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('id_servicio', $this->getAttribute('id_servicio'))
                    ->where('id_producto', $this->getAttribute('id_producto'));
    }

    /**
     * Get the servicio that owns the servicio_producto.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    /**
     * Get the producto that owns the servicio_producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
