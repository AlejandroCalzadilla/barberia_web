<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    // use HasRoles; // Comentado: Ya no usamos Spatie Permission

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'nombre',
        'apellido',
        'telefono',
        'direccion',
        'tipo_usuario',
        'estado',
        'is_propietario',
        'is_barbero',
        'is_cliente',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_propietario' => 'boolean',
        'is_barbero' => 'boolean',
        'is_cliente' => 'boolean',
    ];/
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes with default values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'tipo_usuario' => 'cliente',
        'estado' => 'activo',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Always load these relations
     */
    protected $with = ['cliente', 'barbero'];

    /**
     * Relación con el modelo Barbero
     */
    public function barbero()
    {
        return $this->hasOne(Barbero::class, 'id_usuario', 'id');
    }

    /**
     * Relación con el modelo Cliente
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_usuario', 'id');
    }


    public function getRedirectRoute(): string
    {
        // Si es cliente → catálogo de servicios
        if ($this->cliente) {
            return route('servicios.catalogo');
        }
        
        // Si es barbero → dashboard
        if ($this->barbero) {
            return route('reservas.index');
        }
        
        // Si es admin → dashboard
        return route('reportes.index');
    }
}

