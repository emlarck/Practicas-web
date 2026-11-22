@extends('layouts.app')

@section('title', 'Partida #' . $game->id . ' - Juego del Gato')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Partida #{{ $game->id }}</h2>
        <small class="text-muted">
            <strong>X:</strong> {{ $game->playerX?->name }} ·
            <strong>O:</strong> <span id="player-o-name">{{ $game->playerO?->name ?? 'Esperando rival...' }}</span>
            @if ($mySymbol)
                · <strong>Tu símbolo:</strong> <span class="badge bg-primary">{{ $mySymbol }}</span>
            @endif
        </small>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('games.index') }}" class="btn btn-outline-secondary btn-sm">Volver a partidas</a>
        @if ($mySymbol && in_array($game->status, ['waiting', 'playing'], true))
            <form action="{{ route('games.leave', $game) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Abandonar partida</button>
            </form>
        @endif
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body text-center">
        <div id="game-status-banner" class="alert alert-info mb-4">
            @if ($game->status === 'waiting')
                Esperando a que otro jugador se una a la partida...
            @elseif ($game->status === 'playing')
                Turno actual: <strong>{{ $game->current_turn }}</strong>
                @if ($mySymbol === $game->current_turn)
                    — <strong>¡Es tu turno!</strong>
                @elseif ($mySymbol)
                    — Esperando el movimiento del rival...
                @endif
            @else
                Partida finalizada. Resultado: <strong>{{ $game->result }}</strong>
                @if ($game->winner)
                    (Ganador: <strong>{{ $game->winner->name }}</strong>)
                @endif
            @endif
        </div>

        <form id="move-form" action="{{ route('games.move', $game) }}" method="POST">
            @csrf
            <input type="hidden" name="cell" id="selected-cell" value="">

            <div id="board-grid" class="d-grid mx-auto" style="grid-template-columns: repeat(3, 95px); gap: 10px; max-width: 305px;">
                @for ($i = 0; $i < 9; $i++)
                    @php
                        $val = $game->board[$i] ?? null;
                        $canClick = $game->status === 'playing'
                            && $mySymbol !== null
                            && $mySymbol === $game->current_turn
                            && $val === null;
                    @endphp
                    <button type="button"
                            data-cell="{{ $i }}"
                            class="cell-btn btn {{ $val ? 'btn-dark' : 'btn-outline-dark' }} fw-bold fs-2 d-flex align-items-center justify-content-center"
                            style="width: 95px; height: 95px;"
                            {{ $canClick ? '' : 'disabled' }}>
                        {{ $val ?? '' }}
                    </button>
                @endfor
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const stateUrl = "{{ route('games.state', $game) }}";
    const moveForm = document.getElementById('move-form');
    const selectedCellInput = document.getElementById('selected-cell');
    const statusBanner = document.getElementById('game-status-banner');
    const playerOName = document.getElementById('player-o-name');
    const buttons = Array.from(document.querySelectorAll('.cell-btn'));

    function attachClickHandlers() {
        buttons.forEach((btn) => {
            btn.onclick = function () {
                if (btn.disabled) return;
                selectedCellInput.value = btn.getAttribute('data-cell');
                moveForm.submit();
            };
        });
    }

    function renderState(data) {
        if (playerOName) {
            playerOName.textContent = data.player_o;
        }

        if (data.status === 'waiting') {
            statusBanner.className = 'alert alert-info mb-4';
            statusBanner.innerHTML = 'Esperando a que otro jugador se una a la partida...';
        } else if (data.status === 'playing') {
            statusBanner.className = 'alert alert-primary mb-4';
            let turnMsg = data.is_my_turn
                ? ' — <strong>¡Es tu turno!</strong>'
                : (data.my_symbol ? ' — Esperando el movimiento del rival...' : '');
            statusBanner.innerHTML = `Turno actual: <strong>${data.current_turn}</strong>${turnMsg}`;
        } else {
            statusBanner.className = 'alert alert-secondary mb-4';
            let winnerText = data.winner_name ? ` (Ganador: <strong>${data.winner_name}</strong>)` : '';
            statusBanner.innerHTML = `Partida finalizada · Resultado: <strong>${data.result ?? data.status}</strong>${winnerText}`;
        }

        buttons.forEach((btn, idx) => {
            const val = data.board[idx] ?? null;
            btn.textContent = val ?? '';
            if (val) {
                btn.classList.remove('btn-outline-dark');
                btn.classList.add('btn-dark');
            } else {
                btn.classList.remove('btn-dark');
                btn.classList.add('btn-outline-dark');
            }
            btn.disabled = !(data.is_my_turn && val === null);
        });
    }

    attachClickHandlers();

    setInterval(async () => {
        try {
            const res = await fetch(stateUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            renderState(data);
        } catch (e) {
            // Ignorar errores transitorios de red durante el polling
        }
    }, 2000);
})();
</script>
@endpush