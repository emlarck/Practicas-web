<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Reservation::with(['computer', 'user']);

        // El estudiante solo puede ver sus propias reservaciones
        if ($user->role === 'estudiante') {
            $query->where('user_id', $user->id);
        }

        $reservations = $query
            ->orderByDesc('reserved_at')
            ->get();

        return view('reservations.index', compact('reservations'));
    }

    public function store(Request $request, Computer $computer)
    {
        $validated = $request->validate([
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:8'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ], [
            'duration_hours.integer' => 'La duración debe ser un número entero.',
            'duration_hours.min'     => 'La duración mínima es de 1 hora.',
            'duration_hours.max'     => 'La duración máxima es de 8 horas.',
        ]);

$hours = (int) ($validated['duration_hours'] ?? 2);
        $user = $request->user();

        try {
            DB::transaction(function () use ($computer, $user, $hours, $validated) {
                // Bloqueo pesimista del equipo para evitar que dos usuarios lo reserven a la vez
                $lockedComputer = Computer::where('id', $computer->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $hasActiveReservation = Reservation::where('computer_id', $lockedComputer->id)
                    ->where('status', 'activa')
                    ->lockForUpdate()
                    ->exists();

                if ($lockedComputer->status !== 'disponible' || $hasActiveReservation) {
                    throw new \RuntimeException('El equipo ya no está disponible para reservar.');
                }

                $now = now();

                Reservation::create([
                    'user_id'     => $user->id,
                    'computer_id' => $lockedComputer->id,
                    'reserved_at' => $now,
'expires_at' => $now->copy()->addHours((int) $hours),
                    'status'      => 'activa',
                    'notes'       => $validated['notes'] ?? null,
                ]);

                $lockedComputer->update([
                    'status' => 'reservado',
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('computers.index')
                ->withErrors(['reservation' => $e->getMessage()]);
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación realizada correctamente.');
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $user = $request->user();

        // Evitar que un estudiante cancele reservaciones de otro usuario
        if ($user->role === 'estudiante' && $reservation->user_id !== $user->id) {
            abort(403, 'No tienes permiso para cancelar esta reservación.');
        }

        if ($reservation->status !== 'activa') {
            return redirect()->route('reservations.index')
                ->withErrors(['reservation' => 'Solo se pueden cancelar reservaciones activas.']);
        }

        DB::transaction(function () use ($reservation) {
            $lockedReservation = Reservation::where('id', $reservation->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedReservation->update([
                'status' => 'cancelada',
            ]);

            $lockedComputer = Computer::where('id', $lockedReservation->computer_id)
                ->lockForUpdate()
                ->first();

            if ($lockedComputer) {
                $stillReserved = Reservation::where('computer_id', $lockedComputer->id)
                    ->where('status', 'activa')
                    ->exists();

                if (!$stillReserved && $lockedComputer->status === 'reservado') {
                    $lockedComputer->update(['status' => 'disponible']);
                }
            }
        });

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación cancelada correctamente.');
    }
}