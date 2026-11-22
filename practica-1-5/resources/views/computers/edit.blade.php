@extends('layouts.app')

@section('title', 'Editar Equipo')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Editar equipo: {{ $computer->code }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('computers.update', $computer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code" class="form-label">Código</label>
                            <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $computer->code) }}" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $computer->name) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="lab_room" class="form-label">Laboratorio</label>
                            <input type="text" name="lab_room" id="lab_room" class="form-control" value="{{ old('lab_room', $computer->lab_room) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="disponible" {{ old('status', $computer->status) === 'disponible' ? 'selected' : '' }}>Disponible</option>
                                <option value="reservado" {{ old('status', $computer->status) === 'reservado' ? 'selected' : '' }}>Reservado</option>
                                <option value="mantenimiento" {{ old('status', $computer->status) === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="specifications" class="form-label">Especificaciones</label>
                        <input type="text" name="specifications" id="specifications" class="form-control" value="{{ old('specifications', $computer->specifications) }}">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('computers.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar equipo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection