<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'id_usuario',
        'fecha_nacimiento',
        'ci',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
