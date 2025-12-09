<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function __construct()
    {
       /*  $this->middleware('permission:productos.view')->only(['index']);
        $this->middleware('permission:productos.create')->only(['create','store']);
        $this->middleware('permission:productos.update')->only(['edit','update']);
        $this->middleware('permission:productos.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $q = $request->string('q');
        $categoriaId = $request->integer('categoria');

        $productos = Producto::query()
            ->with('categoria:id_categoria,nombre')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'ilike', "%{$q}%")
                        ->orWhere('codigo', 'ilike', "%{$q}%");
                });
            })
            ->when($categoriaId, fn($query) => $query->where('id_categoria', $categoriaId))
            ->orderByDesc('id_producto')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get(['id_categoria','nombre']);

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'filters' => [
                'q' => $q,
                'categoria' => $categoriaId,
            ],
        ]);
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get(['id_categoria','nombre']);
        return Inertia::render('Productos/Create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_categoria' => ['required', 'exists:categoria,id_categoria'],
            'codigo' => ['required', 'string', 'max:50', 'unique:producto,codigo'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_actual' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'unidad_medida' => ['nullable', 'string', 'max:20'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'imagenurl' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        // Procesar imagen si existe
        if ($request->hasFile('imagenurl')) {
            $imagen = $request->file('imagenurl');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $ruta = $imagen->storeAs('productos', $nombreImagen, 'public');
            $data['imagenurl'] = '/storage/' . $ruta;
        }

        Producto::create($data);
        return redirect()->route('productos.index');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get(['id_categoria','nombre']);
        return Inertia::render('Productos/Edit', [
            'producto' => $producto->load('categoria:id_categoria,nombre'),
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'id_categoria' => ['required', 'exists:categoria,id_categoria'],
            'codigo' => ['required', 'string', 'max:50', 'unique:producto,codigo,' . $producto->id_producto . ',id_producto'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_actual' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'unidad_medida' => ['nullable', 'string', 'max:20'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'imagenurl' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        // Procesar imagen solo si se subió un archivo nuevo
        if ($request->hasFile('imagenurl')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagenurl) {
                $rutaAntigua = str_replace('/storage/', '', $producto->imagenurl);
                Storage::disk('public')->delete($rutaAntigua);
            }
            
            $imagen = $request->file('imagenurl');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $ruta = $imagen->storeAs('productos', $nombreImagen, 'public');
            $data['imagenurl'] = '/storage/' . $ruta;
        } else {
            // Si no hay archivo nuevo, removemos 'imagenurl' de los datos validados
            // para no sobrescribir la imagen existente
            unset($data['imagenurl']);
        }

        $producto->update($data);
        return redirect()->route('productos.index');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index');
    }
}
