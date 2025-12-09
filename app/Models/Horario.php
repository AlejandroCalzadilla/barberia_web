<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Barbero;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horario';
    protected $primaryKey = 'id_horario';

    protected $fillable = [
        'id_barbero',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];

    public function barbero()
    {
        return $this->belongsTo(Barbero::class, 'id_barbero', 'id_barbero');
    }
}
