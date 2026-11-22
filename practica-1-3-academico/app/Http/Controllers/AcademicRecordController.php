<?php

namespace App\Http\Controllers;

use App\Models\AcademicRecord;
use App\Models\User;
use Illuminate\Http\Request;

class AcademicRecordController extends Controller
{
    /**
     * Columnas permitidas para ordenar la consulta.
     */
    private const SORTABLE_COLUMNS = [
        'student_name' => 'student_name',
        'grade'        => 'grade',
        'absences'     => 'absences',
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $sortBy = $request->input('sort_by', 'student_name');
        $direction = strtolower($request->input('direction', 'asc'));

        if (!array_key_exists($sortBy, self::SORTABLE_COLUMNS)) {
            $sortBy = 'student_name';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = AcademicRecord::query();

        // Si es alumno, solo puede ver su propia información académica
        if ($user->role === 'alumno') {
            $query->where('user_id', $user->id);
        }

        $records = $query
            ->orderBy(self::SORTABLE_COLUMNS[$sortBy], $direction)
            ->orderBy('id', 'asc')
            ->get();

        return view('records.index', compact('records', 'sortBy', 'direction'));
    }

    public function create()
    {
        $alumnos = User::where('role', 'alumno')->orderBy('name')->get();
        return view('records.create', compact('alumnos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => ['nullable', 'exists:users,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'absences'     => ['required', 'integer', 'min:0', 'max:200'],
            'grade'        => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'student_name.required' => 'El nombre del alumno es obligatorio.',
            'absences.required'     => 'El número de faltas es obligatorio.',
            'absences.integer'      => 'El número de faltas debe ser un número entero.',
            'absences.min'          => 'Las faltas no pueden ser menores a 0.',
            'absences.max'          => 'El número de faltas excede el límite permitido.',
            'grade.required'        => 'La calificación es obligatoria.',
            'grade.numeric'         => 'La calificación debe ser un valor numérico.',
            'grade.min'             => 'La calificación no puede ser menor a 0.',
            'grade.max'             => 'La calificación no puede ser mayor a 100.',
        ]);

        $equivalence = AcademicRecord::calculateEquivalence((float) $validated['grade']);

        AcademicRecord::create([
            'user_id'           => $validated['user_id'] ?? null,
            'student_name'      => $validated['student_name'],
            'absences'          => (int) $validated['absences'],
            'grade'             => (float) $validated['grade'],
            'grade_equivalence' => $equivalence,
        ]);

        return redirect()->route('records.index')
            ->with('success', 'Registro académico guardado correctamente.');
    }

    public function edit(AcademicRecord $record)
    {
        $alumnos = User::where('role', 'alumno')->orderBy('name')->get();
        return view('records.edit', compact('record', 'alumnos'));
    }

    public function update(Request $request, AcademicRecord $record)
    {
        $validated = $request->validate([
            'user_id'      => ['nullable', 'exists:users,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'absences'     => ['required', 'integer', 'min:0', 'max:200'],
            'grade'        => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'student_name.required' => 'El nombre del alumno es obligatorio.',
            'absences.required'     => 'El número de faltas es obligatorio.',
            'absences.integer'      => 'El número de faltas debe ser un número entero.',
            'absences.min'          => 'Las faltas no pueden ser menores a 0.',
            'absences.max'          => 'El número de faltas excede el límite permitido.',
            'grade.required'        => 'La calificación es obligatoria.',
            'grade.numeric'         => 'La calificación debe ser un valor numérico.',
            'grade.min'             => 'La calificación no puede ser menor a 0.',
            'grade.max'             => 'La calificación no puede ser mayor a 100.',
        ]);

        $equivalence = AcademicRecord::calculateEquivalence((float) $validated['grade']);

        $record->update([
            'user_id'           => $validated['user_id'] ?? null,
            'student_name'      => $validated['student_name'],
            'absences'          => (int) $validated['absences'],
            'grade'             => (float) $validated['grade'],
            'grade_equivalence' => $equivalence,
        ]);

        return redirect()->route('records.index')
            ->with('success', 'Registro académico actualizado correctamente.');
    }

    public function destroy(AcademicRecord $record)
    {
        $record->delete();

        return redirect()->route('records.index')
            ->with('success', 'Registro académico eliminado correctamente.');
    }
}