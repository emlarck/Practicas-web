@extends('layouts.app')

@section('title', 'Listado de Usuarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Usuarios</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('inicio') }}" class="btn btn-outline-secondary">
            Volver al Inicio
        </a>
        @if (auth()->user()->role === 'admin')
            <a href="{{ route('users.trashed') }}" class="btn btn-outline-warning">
                Eliminados (Soft Delete)
            </a>
        @endif
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            + Nuevo Usuario
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo electrónico</th>
                    <th>Rol</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-info text-dark' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                            @if (auth()->user()->role === 'admin')
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline-flex align-items-center gap-1 ms-1">
                                    @csrf
                                    @method('DELETE')

                                    <select name="delete_type" class="form-select form-select-sm w-auto" aria-label="Tipo de eliminación">
                                        <option value="soft">Lógica (Soft Delete)</option>
                                        <option value="force">Permanente (Delete)</option>
                                    </select>

                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Seguro que deseas eliminar a {{ $user->name }}?')">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection