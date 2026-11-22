<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComputerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Computer::query();

        // El estudiante consulta los equipos disponibles; el administrador ve todos los equipos
        if ($user->role === 'estudiante') {
            $query->where('status', 'disponible');
        } elseif ($request->filled('status') && in_array($request->input('status'), ['disponible', 'reservado', 'mantenimiento'], true)) {
            $query->where('status', $request->input('status'));
        }

        $computers = $query->orderBy('code')->get();

        return view('computers.index', compact('computers'));
    }

    public function create()
    {
        return view('computers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:50', 'unique:computers,code'],
            'name'           => ['required', 'string', 'max:255'],
            'specifications' => ['nullable', 'string', 'max:255'],
            'lab_room'       => ['nullable', 'string', 'max:100'],
            'status'         => ['required', Rule::in(['disponible', 'reservado', 'mantenimiento'])],
        ], [
            'code.required'   => 'El código del equipo es obligatorio.',
            'code.unique'     => 'Ya existe un equipo con ese código.',
            'name.required'   => 'El nombre del equipo es obligatorio.',
            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'El estado no es válido.',
        ]);

        Computer::create($validated);

        return redirect()->route('computers.index')
            ->with('success', 'Equipo registrado correctamente.');
    }

    public function edit(Computer $computer)
    {
        return view('computers.edit', compact('computer'));
    }

    public function update(Request $request, Computer $computer)
    {
        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:50', Rule::unique('computers', 'code')->ignore($computer->id)],
            'name'           => ['required', 'string', 'max:255'],
            'specifications' => ['nullable', 'string', 'max:255'],
            'lab_room'       => ['nullable', 'string', 'max:100'],
            'status'         => ['required', Rule::in(['disponible', 'reservado', 'mantenimiento'])],
        ], [
            'code.required'   => 'El código del equipo es obligatorio.',
            'code.unique'     => 'Ya existe otro equipo con ese código.',
            'name.required'   => 'El nombre del equipo es obligatorio.',
            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'El estado no es válido.',
        ]);

        $computer->update($validated);

        return redirect()->route('computers.index')
            ->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(Computer $computer)
    {
        $computer->delete();

        return redirect()->route('computers.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }
}