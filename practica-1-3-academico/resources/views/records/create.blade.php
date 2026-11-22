@extends('layouts.app')

@section('title', 'Registrar Alumno')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Registrar información académica</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('records.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="user_id" class="form-label">Vincular con cuenta de Alumno (opcional)</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">-- Sin cuenta vinculada --</option>
                            @foreach ($alumnos as $alumno)
                                <option value="{{ $alumno->id }}" {{ old('user_id') == $alumno->id ? 'selected' : '' }}>
                                    {{ $alumno->name }} ({{ $alumno->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="student_name" class="form-label">Nombre del alumno</label>
                        <input type="text" name="student_name" id="student_name" class="form-control" value="{{ old('student_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="absences" class="form-label">Número de faltas</label>
                        <input type="number" name="absences" id="absences" min="0" max="200" class="form-control" value="{{ old('absences', 0) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="grade" class="form-label">Calificación numérica (0 - 100)</label>
                        <input type="number" step="0.01" name="grade" id="grade" min="0" max="100" class="form-control" value="{{ old('grade') }}" required>
                        <div class="form-text">La equivalencia (NA, SA, DE, AU) se calculará automáticamente al guardar.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('records.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection