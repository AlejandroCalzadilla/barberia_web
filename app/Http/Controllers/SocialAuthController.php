<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect to OAuth provider
     */
    public function redirectToProvider($provider)
    {
        $allowedProviders = ['google', 'facebook', 'github'];
        
        if (!in_array($provider, $allowedProviders)) {
            return redirect()->route('register')->withErrors(['error' => 'Proveedor no soportado']);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Buscar usuario existente por email
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if ($user) {
                // Usuario existe - actualizar info del proveedor si es necesario
                if (!$user->provider || !$user->provider_id) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                    ]);
                }
            } else {
                // Crear nuevo usuario con rol de cliente por defecto
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuario',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(24)), // Password aleatorio
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'email_verified_at' => now(), // Auto-verificar email de OAuth
                    'is_propietario' => false,
                    'is_barbero' => false,
                    'is_cliente' => true, // Por defecto es cliente
                    'tipo_usuario' => 'cliente',
                    'estado' => 'activo',
                ]);

                // Crear registro de Cliente asociado
                Cliente::create([
                    'id_usuario' => $user->id,
                    'fecha_nacimiento' => null,
                    'ci' => null,
                ]);
            }

            // Iniciar sesión
            Auth::login($user, true);

            // Redirigir según el rol usando los campos booleanos
            if ($user->is_cliente) {
                return redirect()->route('servicios.catalogo');
            }
            
            if ($user->is_barbero) {
                return redirect()->route('dashboard');
            }
            
            if ($user->is_propietario) {
                return redirect()->route('dashboard');
            }
            
            // Fallback por si no tiene ningún rol asignado
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('OAuth Error: ' . $e->getMessage(), [
                'provider' => $provider,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('register')->withErrors([
                'error' => 'Error al autenticar con ' . ucfirst($provider) . ': ' . $e->getMessage()
            ]);
        }
    }
}
