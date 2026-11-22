@extends('layouts.app')

@section('title', 'Equipos de Cómputo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">
            @if (auth()->user()->role === 'admin')
                Administración de Equipos de Cómputo
            @else
                Equipos Disponibles
            @endif
        </h2>
        <small class="text-muted">Usuario activo: {{ auth()->user()->name }}</small>
    </div>

    @if (auth()->user()->role === 'admin')
        <a href="{{ route('computers.create') }}" class="btn btn-primary">
            + Registrar Equipo
        </a>
    @endif
</div>

@if (auth()->user()->role === 'admin')
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form action="{{ route('computers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="status" class="col-form-label small">Filtrar por estado:</label>
                </div>
                <div class="col-auto">
                    <select name="status" id="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="disponible" {{ request('status') === 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="reservado" {{ request('status') === 'reservado' ? 'selected' : '' }}>Reservado</option>
                        <option value="mantenimiento" {{ request('status') === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    </select>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="row g-3">
    @forelse ($computers as $computer)
        @php
            $badgeClass = match ($computer->status) {
                'disponible' => 'bg-success',
                'reservado' => 'bg-warning text-dark',
                default => 'bg-secondary',
            };
        @endphp

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-dark">{{ $computer->code }}</span>
                            <h5 class="card-title mt-1 mb-0">{{ $computer->name }}</h5>
                        </div>
                        <span class="badge {{ $badgeClass }} text-uppercase">
                            {{ $computer->status }}
                        </span>
                    </div>

                    <p class="text-muted small mb-1">
                        <strong>Laboratorio:</strong> {{ $computer->lab_room ?? 'No asignado' }}
                    </p>
                    <p class="card-text small flex-grow-1">
                        {{ $computer->specifications ?? 'Sin especificaciones registradas.' }}
                    </p>

                    <hr>

                    @if ($computer->status === 'disponible')
                        <form action="{{ route('reservations.store', $computer) }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <select name="duration_hours" class="form-select form-select-sm" style="max-width: 110px;">
                                <option value="1">1 hora</option>
                                <option value="2" selected>2 horas</option>
                                <option value="3">3 horas</option>
                                <option value="4">4 horas</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                                Reservar equipo
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" disabled>
                            No disponible
                        </button>
                    @endif

                    @if (auth()->user()->role === 'admin')
                        <div class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                            <a href="{{ route('computers.edit', $computer) }}" class="btn btn-sm btn-outline-primary">
                                Editar
                            </a>
                            <form action="{{ route('computers.destroy', $computer) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Eliminar el equipo {{ $computer->code }}?')">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center mb-0">
                No hay equipos disponibles en este momento.
            </div>
        </div>
    @endforelse
</div>
@endsection