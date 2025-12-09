<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function __construct()
    {
       /*  $this->middleware('permission:usuarios.view')->only(['index']);
        $this->middleware('permission:usuarios.create')->only(['create','store']);
        $this->middleware('permission:usuarios.update')->only(['edit','update']);
        $this->middleware('permission:usuarios.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $q = $request->string('q');
        $tipoFilter = $request->string('tipo');

        $usuarios = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('email', 'ilike', "%{$q}%")
                        ->orWhere('telefono', 'ilike', "%{$q}%");
                });
            })
            ->when($tipoFilter->isNotEmpty(), fn($query) => $query->where('tipo_usuario', $tipoFilter))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => [
                'q' => $q,
                'tipo' => $tipoFilter,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Usuarios/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'tipo_usuario' => ['required', 'in:propietario,barbero,cliente'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            // Campos para barbero
            'especialidad' => ['nullable', 'string', 'max:255'],
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
            // Campos para cliente
            'fecha_nacimiento' => ['nullable', 'date'],
            'ci' => ['nullable', 'string', 'max:20'],
        ]);

        $data['password'] = Hash::make($data['password']);
        
        // Asignar roles booleanos
        $data['is_propietario'] = $data['tipo_usuario'] === 'propietario';
        $data['is_barbero'] = $data['tipo_usuario'] === 'barbero';
        $data['is_cliente'] = $data['tipo_usuario'] === 'cliente';

        // Manejar foto de perfil si se subió
        $fotoPerfilPath = null;
        if ($request->hasFile('foto_perfil')) {
            $fotoPerfilPath = $request->file('foto_perfil')->store('barberos', 'public');
        }

        // Extraer datos específicos
        $barberoData = [
            'especialidad' => $data['especialidad'] ?? null,
            'foto_perfil' => $fotoPerfilPath,
            'estado' => 'disponible',
        ];
        
        $clienteData = [
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'ci' => $data['ci'] ?? null,
        ];

        // Limpiar campos extras del usuario
        unset($data['especialidad'], $data['foto_perfil'], $data['fecha_nacimiento'], $data['ci']);

        $usuario = User::create($data);

        // Crear registro de barbero si aplica
        if ($data['is_barbero'] && ($barberoData['especialidad'] || $barberoData['foto_perfil'])) {
            $usuario->barbero()->create($barberoData);
        }

        // Crear registro de cliente si aplica
        if ($data['is_cliente'] && ($clienteData['fecha_nacimiento'] || $clienteData['ci'])) {
            $usuario->cliente()->create($clienteData);
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $usuario)
    {
        $usuario->load(['barbero', 'cliente']);
        
        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'tipo_usuario' => ['required', 'in:propietario,barbero,cliente'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            // Campos para barbero
            'especialidad' => ['nullable', 'string', 'max:255'],
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
            // Campos para cliente
            'fecha_nacimiento' => ['nullable', 'date'],
            'ci' => ['nullable', 'string', 'max:20'],
        ]);

        // Limpiar campos vacíos que vienen como string vacío desde FormData
        foreach ($data as $key => $value) {
            if ($value === '' && !in_array($key, ['password', 'password_confirmation'])) {
                $data[$key] = null;
            }
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Asignar roles booleanos
        $data['is_propietario'] = $data['tipo_usuario'] === 'propietario';
        $data['is_barbero'] = $data['tipo_usuario'] === 'barbero';
        $data['is_cliente'] = $data['tipo_usuario'] === 'cliente';

        // Manejar foto de perfil si se subió una nueva
        $fotoPerfilPath = null;
        if ($request->hasFile('foto_perfil')) {
            $fotoPerfilPath = $request->file('foto_perfil')->store('barberos', 'public');
        } else {
            // Mantener la foto anterior si existe
            $fotoPerfilPath = $usuario->barbero?->foto_perfil;
        }

        // Extraer datos específicos
        $barberoData = [
            'especialidad' => $data['especialidad'] ?? null,
            'foto_perfil' => $fotoPerfilPath,
            'estado' => 'disponible',
        ];
        
        $clienteData = [
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'ci' => $data['ci'] ?? null,
        ];

        // Limpiar campos extras del usuario
        unset($data['especialidad'], $data['foto_perfil'], $data['fecha_nacimiento'], $data['ci']);

        $usuario->update($data);

        // Actualizar o crear registro de barbero
        if ($data['is_barbero']) {
            $usuario->barbero()->updateOrCreate(
                ['id_usuario' => $usuario->id],
                $barberoData
            );
        } else {
            // Eliminar barbero si cambió de tipo
            $usuario->barbero()->delete();
        }

        // Actualizar o crear registro de cliente
        if ($data['is_cliente']) {
            $usuario->cliente()->updateOrCreate(
                ['id_usuario' => $usuario->id],
                $clienteData
            );
        } else {
            // Eliminar cliente si cambió de tipo
            $usuario->cliente()->delete();
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $usuario)
    {
        try {
            $usuario->delete();
            return redirect()
                ->route('usuarios.index')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}
