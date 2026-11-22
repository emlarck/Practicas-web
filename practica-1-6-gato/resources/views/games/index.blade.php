@extends('layouts.app')

@section('title', 'Sala de Partidas - Juego del Gato')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Sala de Partidas</h2>
        <small class="text-muted">Jugador / Usuario activo: <strong>{{ auth()->user()->name }}</strong></small>
    </div>

    <form action="{{ route('games.store') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">+ Crear nueva partida</button>
    </form>
</div>

<div class="row g-4">
    {{-- Partidas disponibles para unirse --}}
    <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Partidas disponibles para unirse</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Creador (X)</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($openGames as $openGame)
                            <tr>
                                <td>{{ $openGame->id }}</td>
                                <td>{{ $openGame->playerX?->name }}</td>
                                <td><span class="badge bg-warning text-dark">Esperando rival</span></td>
                                <td class="text-end">
                                    <form action="{{ route('games.join', $openGame) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Unirse (O)</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    No hay partidas abiertas de otros jugadores en este momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Partidas activas --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    {{ auth()->user()->role === 'admin' ? 'Todas las partidas en curso / espera' : 'Mis partidas activas' }}
                </h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Jugador X</th>
                            <th>Jugador O</th>
                            <th>Turno</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeGames as $game)
                            @php
                                $isParticipant = in_array(auth()->id(), [$game->player_x_id, $game->player_o_id], true);
                            @endphp
                            <tr>
                                <td>{{ $game->id }}</td>
                                <td>{{ $game->playerX?->name }}</td>
                                <td>{{ $game->playerO?->name ?? 'Esperando...' }}</td>
                                <td><span class="badge bg-dark">{{ $game->current_turn }}</span></td>
                                <td>
                                    <span class="badge {{ $game->status === 'playing' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                        {{ $game->status === 'playing' ? 'En curso' : 'Esperando' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($isParticipant)
                                        <a href="{{ route('games.show', $game) }}" class="btn btn-sm btn-outline-primary">
                                            Entrar al tablero
                                        </a>
                                        <form action="{{ route('games.leave', $game) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Abandonar</button>
                                        </form>
                                    @endif

                                    @if (auth()->user()->role === 'admin')
                                        <form action="{{ route('games.cancel', $game) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('¿Cancelar la partida #{{ $game->id }}?')">
                                                Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    No hay partidas activas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Lista de jugadores disponibles --}}
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Jugadores registrados</h5>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($availablePlayers as $player)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $player->name }}</strong>
                            <div class="small text-muted">{{ $player->email }}</div>
                        </div>
                        <span class="badge bg-light text-dark border text-uppercase">{{ $player->role }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center">No hay otros jugadores registrados.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- Historial de partidas --}}
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            {{ auth()->user()->role === 'admin' ? 'Historial general de partidas' : 'Mi historial de partidas' }}
        </h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Jugador X</th>
                    <th>Jugador O</th>
                    <th>Ganador</th>
                    <th>Resultado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historyGames as $hist)
                    @php
                        $resultLabel = match ($hist->result) {
                            'win_x'     => 'Victoria de X',
                            'win_o'     => 'Victoria de O',
                            'draw'      => 'Empate',
                            'abandoned' => 'Abandonada',
                            'cancelled' => 'Cancelada por admin',
                            default     => ucfirst((string) $hist->result),
                        };
                    @endphp
                    <tr>
                        <td>{{ $hist->id }}</td>
                        <td>{{ $hist->playerX?->name }}</td>
                        <td>{{ $hist->playerO?->name ?? '—' }}</td>
                        <td>{{ $hist->winner?->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $resultLabel }}</span></td>
                        <td>{{ $hist->updated_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Aún no hay partidas finalizadas o canceladas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection