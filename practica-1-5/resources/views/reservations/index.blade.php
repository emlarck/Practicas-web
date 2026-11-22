@extends('layouts.app')

@section('title', 'Reservaciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">
            @if (auth()->user()->role === 'admin')
                Todas las Reservaciones
            @else
                Mis Reservaciones
            @endif
        </h2>
        <small class="text-muted">Sesión de {{ auth()->user()->name }}</small>
    </div>
    <a href="{{ route('computers.index') }}" class="btn btn-outline-dark btn-sm">Ver equipos</a>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Equipo</th>
                    @if (auth()->user()->role === 'admin')
                        <th>Usuario</th>
                    @endif
                    <th>Reservado</th>
                    <th>Vence</th>
                    <th>Estado</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    @php
                        $resBadge = match ($reservation->status) {
                            'activa' => 'bg-success',
                            'cancelada' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <tr>
                        <td>{{ $reservation->id }}</td>
                        <td>
                            <strong>{{ $reservation->computer?->code }}</strong> - {{ $reservation->computer?->name }}
                        </td>
                        @if (auth()->user()->role === 'admin')
                            <td>{{ $reservation->user?->name }} ({{ $reservation->user?->email }})</td>
                        @endif
                        <td>{{ $reservation->reserved_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $reservation->expires_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $resBadge }} text-uppercase">{{ $reservation->status }}</span>
                        </td>
                        <td class="text-end">
                            @if ($reservation->status === 'activa')
                                <form action="{{ route('reservations.cancel', $reservation) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Cancelar esta reservación?')">
                                        Cancelar
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="text-center text-muted py-4">
                            No hay reservaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection