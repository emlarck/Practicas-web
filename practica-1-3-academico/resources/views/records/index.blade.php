@extends('layouts.app')

@section('title', 'Registros Académicos')

@section('content')
@php
    $nextDir = fn(string $col) => ($sortBy === $col && $direction === 'asc') ? 'desc' : 'asc';
    $dirArrow = function (string $col) use ($sortBy, $direction) {
        if ($sortBy !== $col) return '';
        return $direction === 'asc' ? ' ↑' : ' ↓';
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Información Académica</h2>
        <small class="text-muted">
            @if (auth()->user()->role === 'profesor')
                Vista de Profesor: gestión completa de alumnos, faltas y calificaciones[cite: 4].
            @else
                Vista de Alumno: consulta de tu información académica[cite: 4].
            @endif
        </small>
    </div>

    @if (auth()->user()->role === 'profesor')
        <a href="{{ route('records.create') }}" class="btn btn-primary">
            + Registrar Alumno
        </a>
    @endif
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small text-muted me-1">Ordenar por:</span>

            <a href="{{ route('records.index', ['sort_by' => 'student_name', 'direction' => $nextDir('student_name')]) }}"
               class="btn btn-sm {{ $sortBy === 'student_name' ? 'btn-dark' : 'btn-outline-secondary' }}">
                Nombre{{ $dirArrow('student_name') }}
            </a>

            <a href="{{ route('records.index', ['sort_by' => 'grade', 'direction' => $nextDir('grade')]) }}"
               class="btn btn-sm {{ $sortBy === 'grade' ? 'btn-dark' : 'btn-outline-secondary' }}">
                Calificación{{ $dirArrow('grade') }}
            </a>

            <a href="{{ route('records.index', ['sort_by' => 'absences', 'direction' => $nextDir('absences')]) }}"
               class="btn btn-sm {{ $sortBy === 'absences' ? 'btn-dark' : 'btn-outline-secondary' }}">
                Faltas{{ $dirArrow('absences') }}
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre del alumno</th>
                    <th>Número de faltas</th>
                    <th>Calificación numérica</th>
                    <th>Equivalencia</th>
                    @if (auth()->user()->role === 'profesor')
                        <th class="text-end">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->student_name }}</td>
                        <td>{{ $record->absences }}</td>
                        <td>{{ number_format((float) $record->grade, 2) }}</td>
                        <td>
                            <x-grade-badge :equivalence="$record->grade_equivalence" />
                        </td>
                        @if (auth()->user()->role === 'profesor')
                            <td class="text-end">
                                <a href="{{ route('records.edit', $record) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                                <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Deseas eliminar el registro de {{ $record->student_name }}?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'profesor' ? 5 : 4 }}" class="text-center text-muted py-4">
                            No hay registros académicos disponibles.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection