@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Detalles del usuario</h4>
                <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-info text-dark' }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">ID</dt>
                    <dd class="col-sm-8">{{ $user->id }}</dd>

                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8">{{ $user->name }}</dd>

                    <dt class="col-sm-4">Correo electrónico</dt>
                    <dd class="col-sm-8">{{ $user->email }}</dd>

                    <dt class="col-sm-4">Registrado el</dt>
                    <dd class="col-sm-8">{{ $user->created_at?->format('d/m/Y H:i') }}</dd>

                    <dt class="col-sm-4">Última actualización</dt>
                    <dd class="col-sm-8">{{ $user->updated_at?->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Regresar al listado</a>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Editar</a>
            </div>
        </div>
    </div>
</div>
@endsection