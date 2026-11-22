<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Jugadores disponibles (otros usuarios registrados)
        $availablePlayers = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        // Partidas disponibles para unirse (creadas por otro jugador y esperando rival)
        $openGames = Game::with('playerX')
            ->where('status', 'waiting')
            ->whereNull('player_o_id')
            ->where('player_x_id', '!=', $user->id)
            ->latest()
            ->get();

        if ($user->role === 'admin') {
            $activeGames = Game::with(['playerX', 'playerO', 'winner'])
                ->whereIn('status', ['waiting', 'playing'])
                ->latest()
                ->get();

            $historyGames = Game::with(['playerX', 'playerO', 'winner'])
                ->whereIn('status', ['finished', 'cancelled'])
                ->latest()
                ->get();
        } else {
            $activeGames = Game::with(['playerX', 'playerO', 'winner'])
                ->whereIn('status', ['waiting', 'playing'])
                ->where(function ($q) use ($user) {
                    $q->where('player_x_id', $user->id)
                      ->orWhere('player_o_id', $user->id);
                })
                ->latest()
                ->get();

            $historyGames = Game::with(['playerX', 'playerO', 'winner'])
                ->whereIn('status', ['finished', 'cancelled'])
                ->where(function ($q) use ($user) {
                    $q->where('player_x_id', $user->id)
                      ->orWhere('player_o_id', $user->id);
                })
                ->latest()
                ->get();
        }

        return view('games.index', compact(
            'availablePlayers',
            'openGames',
            'activeGames',
            'historyGames'
        ));
    }

    public function store(Request $request)
    {
        $game = Game::create([
            'player_x_id'  => $request->user()->id,
            'player_o_id'  => null,
            'winner_id'    => null,
            'board'        => Game::emptyBoard(),
            'current_turn' => 'X',
            'status'       => 'waiting',
            'result'       => null,
        ]);

        return redirect()->route('games.show', $game)
            ->with('success', 'Partida creada. Esperando a que otro jugador se una (eres X).');
    }

    public function join(Request $request, Game $game)
    {
        $user = $request->user();

        try {
            DB::transaction(function () use ($game, $user) {
                $lockedGame = Game::where('id', $game->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedGame->status !== 'waiting' || $lockedGame->player_o_id !== null) {
                    throw new \RuntimeException('Esta partida ya no está disponible para unirse.');
                }

                if ($lockedGame->player_x_id === $user->id) {
                    throw new \RuntimeException('No puedes unirte a tu propia partida.');
                }

                $lockedGame->update([
                    'player_o_id'  => $user->id,
                    'status'       => 'playing',
                    'current_turn' => 'X',
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('games.index')
                ->withErrors(['game' => $e->getMessage()]);
        }

        return redirect()->route('games.show', $game)
            ->with('success', 'Te has unido a la partida como O. ¡Comienza el juego!');
    }

    public function show(Request $request, Game $game)
    {
        $user = $request->user();
        $game->load(['playerX', 'playerO', 'winner']);

        $isParticipant = in_array($user->id, [$game->player_x_id, $game->player_o_id], true);

        if ($user->role !== 'admin' && !$isParticipant) {
            abort(403, 'No formas parte de esta partida.');
        }

        $mySymbol = null;
        if ($game->player_x_id === $user->id) {
            $mySymbol = 'X';
        } elseif ($game->player_o_id === $user->id) {
            $mySymbol = 'O';
        }

        return view('games.show', compact('game', 'mySymbol'));
    }

    public function state(Request $request, Game $game)
    {
        $user = $request->user();
        $game->load(['playerX', 'playerO', 'winner']);

        $isParticipant = in_array($user->id, [$game->player_x_id, $game->player_o_id], true);
        if ($user->role !== 'admin' && !$isParticipant) {
            abort(403);
        }

        $mySymbol = match ($user->id) {
            $game->player_x_id => 'X',
            $game->player_o_id => 'O',
            default            => null,
        };

        return response()->json([
            'id'           => $game->id,
            'board'        => $game->board,
            'current_turn' => $game->current_turn,
            'status'       => $game->status,
            'result'       => $game->result,
            'winner_name'  => $game->winner?->name,
            'player_x'     => $game->playerX?->name,
            'player_o'     => $game->playerO?->name ?? 'Esperando rival...',
            'my_symbol'    => $mySymbol,
            'is_my_turn'   => $game->status === 'playing' && $mySymbol !== null && $mySymbol === $game->current_turn,
        ]);
    }

    public function move(Request $request, Game $game)
    {
        $validated = $request->validate([
            'cell' => ['required', 'integer', 'min:0', 'max:8'],
        ]);

        $cell = (int) $validated['cell'];
        $user = $request->user();

        try {
            DB::transaction(function () use ($game, $user, $cell) {
                $lockedGame = Game::where('id', $game->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedGame->status !== 'playing') {
                    throw new \RuntimeException('La partida no está en curso.');
                }

                $symbol = match ($user->id) {
                    $lockedGame->player_x_id => 'X',
                    $lockedGame->player_o_id => 'O',
                    default                  => null,
                };

                if ($symbol === null) {
                    throw new \RuntimeException('No tienes permiso para jugar en esta partida.');
                }

                if ($lockedGame->current_turn !== $symbol) {
                    throw new \RuntimeException('No es tu turno. Espera el movimiento del otro jugador.');
                }

                $board = $lockedGame->board ?? Game::emptyBoard();

                if (!empty($board[$cell])) {
                    throw new \RuntimeException('Esa casilla ya está ocupada.');
                }

                $board[$cell] = $symbol;

                $winnerSymbol = $this->checkWinner($board);
                $isDraw = !$winnerSymbol && !in_array(null, $board, true);

                if ($winnerSymbol) {
                    $winnerId = $winnerSymbol === 'X'
                        ? $lockedGame->player_x_id
                        : $lockedGame->player_o_id;

                    $lockedGame->update([
                        'board'     => $board,
                        'status'    => 'finished',
                        'result'    => $winnerSymbol === 'X' ? 'win_x' : 'win_o',
                        'winner_id' => $winnerId,
                    ]);
                } elseif ($isDraw) {
                    $lockedGame->update([
                        'board'  => $board,
                        'status' => 'finished',
                        'result' => 'draw',
                    ]);
                } else {
                    $lockedGame->update([
                        'board'        => $board,
                        'current_turn' => $symbol === 'X' ? 'O' : 'X',
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('games.show', $game)
                ->withErrors(['move' => $e->getMessage()]);
        }

        return redirect()->route('games.show', $game);
    }

    public function leave(Request $request, Game $game)
    {
        $user = $request->user();

        DB::transaction(function () use ($game, $user) {
            $lockedGame = Game::where('id', $game->id)
                ->lockForUpdate()
                ->firstOrFail();

            $isParticipant = in_array($user->id, [$lockedGame->player_x_id, $lockedGame->player_o_id], true);
            if (!$isParticipant) {
                abort(403, 'No formas parte de esta partida.');
            }

            if (in_array($lockedGame->status, ['waiting', 'playing'], true)) {
                $winnerId = null;
                if ($lockedGame->status === 'playing') {
                    $winnerId = $lockedGame->player_x_id === $user->id
                        ? $lockedGame->player_o_id
                        : $lockedGame->player_x_id;
                }

                $lockedGame->update([
                    'status'    => 'finished',
                    'result'    => 'abandoned',
                    'winner_id' => $winnerId,
                ]);
            }
        });

        return redirect()->route('games.index')
            ->with('success', 'Has abandonado la partida.');
    }

    public function cancel(Request $request, Game $game)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Solo el administrador puede cancelar partidas.');
        }

        DB::transaction(function () use ($game) {
            $lockedGame = Game::where('id', $game->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedGame->status, ['waiting', 'playing'], true)) {
                $lockedGame->update([
                    'status' => 'cancelled',
                    'result' => 'cancelled',
                ]);
            }
        });

        return redirect()->route('games.index')
            ->with('success', 'Partida cancelada por el administrador.');
    }

    private function checkWinner(array $board): ?string
    {
        $lines = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8], // Filas
            [0, 3, 6], [1, 4, 7], [2, 5, 8], // Columnas
            [0, 4, 8], [2, 4, 6],          // Diagonales
        ];

        foreach ($lines as [$a, $b, $c]) {
            if ($board[$a] !== null && $board[$a] === $board[$b] && $board[$a] === $board[$c]) {
                return $board[$a];
            }
        }

        return null;
    }
}