@extends('layouts.app')

@section('title', 'Usuarios Eliminados')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Usuarios deshabilitados (Soft Delete)</h2>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Volver al listado activo</a>
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
                    <th>Eliminado el</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('users.restore', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    Restaurar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No hay usuarios eliminados lógicamente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection