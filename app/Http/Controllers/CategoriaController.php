<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function __construct()
    {
       /*  $this->middleware('permission:categorias.view')->only(['index']);
        $this->middleware('permission:categorias.create')->only(['create','store']);
        $this->middleware('permission:categorias.update')->only(['edit','update']);
        $this->middleware('permission:categorias.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $q = $request->string('q');
        $categorias = Categoria::query()
            ->when($q, fn($query) => $query->where('nombre', 'ilike', "%{$q}%"))
            ->orderByDesc('id_categoria')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
            'filters' => ['q' => $q],
        ]);
    }

    public function create()
    {
        return Inertia::render('Categorias/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categoria,nombre'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:activa,inactiva'],
        ]);

        Categoria::create($data);
        return redirect()->route('categorias.index');
    }

    public function edit(Categoria $categoria)
    {
        return Inertia::render('Categorias/Edit', [
            'categoria' => $categoria,
        ]);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categoria,nombre,' . $categoria->id_categoria . ',id_categoria'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:activa,inactiva'],
        ]);

        $categoria->update($data);
        return redirect()->route('categorias.index');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return redirect()->route('categorias.index');
    }
}
