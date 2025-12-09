<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageViewsController extends Controller
{
    /**
     * Obtener el número de visitas de la página actual
     */
    public function getCurrentPageViews(Request $request)
    {
        $path = $request->input('path', $request->path());
        $cacheKey = 'views:' . $path;
        
        \Log::info('PageViewsController - getCurrentPageViews', [
            'path' => $path,
            'cacheKey' => $cacheKey,
            'cache_driver' => config('cache.default'),
            'request_path' => $request->path(),
            'input_path' => $request->input('path')
        ]);
        
        $views = Cache::get($cacheKey, 0);
        
        \Log::info('PageViewsController - Vistas obtenidas', [
            'cacheKey' => $cacheKey,
            'views' => $views
        ]);
        
        return response()->json([
            'success' => true,
            'path' => $path,
            'views' => $views,
            'debug' => [
                'cache_driver' => config('cache.default'),
                'cacheKey' => $cacheKey
            ]
        ]);
    }

    /**
     * Obtener estadísticas de todas las páginas
     */
    public function getAllStats()
    {
        // Obtener todas las claves de cache que empiecen con 'views:'
        $prefix = 'views:';
        $allKeys = Cache::get('all_view_keys', []);
        
        $stats = [];
        foreach ($allKeys as $key) {
            $path = str_replace($prefix, '', $key);
            $views = Cache::get($key, 0);
            
            if ($views > 0) {
                $stats[] = [
                    'path' => $path,
                    'views' => $views
                ];
            }
        }
        
        // Ordenar por número de visitas (descendente)
        usort($stats, fn($a, $b) => $b['views'] - $a['views']);
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'total_pages' => count($stats),
            'total_views' => array_sum(array_column($stats, 'views'))
        ]);
    }

    /**
     * Resetear contador de una página específica
     */
    public function resetPageViews(Request $request)
    {
        $path = $request->input('path');
        $cacheKey = 'views:' . $path;
        
        Cache::forget($cacheKey);
        
        return response()->json([
            'success' => true,
            'message' => 'Contador reseteado',
            'path' => $path
        ]);
    }
}
