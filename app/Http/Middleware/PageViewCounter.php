<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PageViewCounter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $sessionKey = 'viewed:' . $path;
        $cacheKey = 'views:' . $path;

        \Log::info('PageViewCounter - Inicio', [
            'path' => $path,
            'sessionKey' => $sessionKey,
            'cacheKey' => $cacheKey,
            'session_has_key' => $request->session()->has($sessionKey),
            'current_cache_value' => Cache::get($cacheKey, 0)
        ]);

        // Verificamos si la sesión actual YA visitó esta página
        if (!$request->session()->has($sessionKey)) {
            
            // 1. Incrementamos el contador global en cache
            $currentViews = Cache::get($cacheKey, 0);
            $newViews = $currentViews + 1;
            Cache::put($cacheKey, $newViews, now()->addDays(30)); // Persiste 30 días
            
            \Log::info('PageViewCounter - Vista incrementada', [
                'path' => $path,
                'old_views' => $currentViews,
                'new_views' => $newViews,
                'cache_driver' => config('cache.default')
            ]);
            
            // 2. Registrar la clave para estadísticas
            $allKeys = Cache::get('all_view_keys', []);
            if (!in_array($cacheKey, $allKeys)) {
                $allKeys[] = $cacheKey;
                Cache::put('all_view_keys', $allKeys, now()->addDays(30));
                \Log::info('PageViewCounter - Nueva clave registrada', ['cacheKey' => $cacheKey]);
            }
            
            // 3. Marcamos en la sesión del usuario que ya la vio
            // Esto dura hasta que cierren el navegador (o expire la sesión)
            $request->session()->put($sessionKey, true);
            
            \Log::info('PageViewCounter - Sesión marcada', ['sessionKey' => $sessionKey]);
        } else {
            \Log::info('PageViewCounter - Vista ya contada en esta sesión', ['path' => $path]);
        }

        return $next($request);
    }
}
