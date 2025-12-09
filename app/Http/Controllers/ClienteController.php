<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Cliente;
use App\Models\User;

class ClienteController extends Controller
{
    public function __construct()
    {
       /*  $this->middleware('permission:clientes.view')->only(['index']);
        $this->middleware('permission:clientes.create')->only(['create','store']);
        $this->middleware('permission:clientes.update')->only(['edit','update']);
        $this->middleware('permission:clientes.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $q = $request->string('q');

        $clientes = Cliente::query()
            ->with(['user:id,name,email'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('user', function ($uq) use ($q) {
                    $uq->where('name', 'ilike', "%{$q}%")
                       ->orWhere('email', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('id_cliente')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters' => ['q' => $q],
        ]);
    }

    public function create()
    {
        // Solo usuarios tipo cliente que NO tengan un registro en la tabla cliente
        $usuarios = User::where('tipo_usuario', 'cliente')
            ->whereDoesntHave('cliente')
            ->orderBy('name')
            ->get(['id','name','email']);
        return Inertia::render('Clientes/Create', [
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'unique:cliente,id_usuario', 'exists:users,id'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'ci' => ['nullable', 'string', 'max:100'],
        ]);

        Cliente::create($data);
        return redirect()->route('clientes.index');
    }

    public function edit(Cliente $cliente)
    {
        // Usuarios tipo cliente sin asociar O el usuario actual del cliente
        $usuarios = User::where('tipo_usuario', 'cliente')
            ->where(function($query) use ($cliente) {
                $query->whereDoesntHave('cliente')
                      ->orWhere('id', $cliente->id_usuario);
            })
            ->orderBy('name')
            ->get(['id','name','email']);
        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente->load('user:id,name,email'),
            'usuarios' => $usuarios,
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'exists:users,id', 'unique:cliente,id_usuario,' . $cliente->id_cliente . ',id_cliente'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'ci' => ['nullable', 'string', 'max:100'],
        ]);

        $cliente->update($data);
        return redirect()->route('clientes.index');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index');
    }
}
