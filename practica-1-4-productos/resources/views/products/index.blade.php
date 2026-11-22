@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Productos</h2>
        <small class="text-muted">
            Mostrando <strong>{{ $products->total() }}</strong> producto(s) encontrado(s) ·
            Página <strong>{{ $products->currentPage() }}</strong> de <strong>{{ max(1, $products->lastPage()) }}</strong>
        </small>
    </div>

    @if (auth()->user()->role === 'admin')
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            + Nuevo Producto
        </a>
    @endif
</div>

{{-- Filtros, Ordenamiento y Tamaño de Página --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Nombre del producto</label>
                <input type="text"
                       name="search"
                       id="search"
                       class="form-control"
                       placeholder="Buscar por nombre..."
                       value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <label for="category_id" class="form-label">Categoría</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="min_price" class="form-label">Precio mín.</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="min_price"
                       id="min_price"
                       class="form-control"
                       placeholder="0.00"
                       value="{{ request('min_price') }}">
            </div>

            <div class="col-md-2">
                <label for="max_price" class="form-label">Precio máx.</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="max_price"
                       id="max_price"
                       class="form-control"
                       placeholder="9999.99"
                       value="{{ request('max_price') }}">
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="Disponible" {{ request('status') === 'Disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="Agotado" {{ request('status') === 'Agotado' ? 'selected' : '' }}>Agotado</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="sort" class="form-label">Ordenar por</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="created_desc" {{ $sortKey === 'created_desc' ? 'selected' : '' }}>Fecha de registro (Más recientes)</option>
                    <option value="created_asc" {{ $sortKey === 'created_asc' ? 'selected' : '' }}>Fecha de registro (Más antiguos)</option>
                    <option value="name_asc" {{ $sortKey === 'name_asc' ? 'selected' : '' }}>Nombre (A - Z)</option>
                    <option value="name_desc" {{ $sortKey === 'name_desc' ? 'selected' : '' }}>Nombre (Z - A)</option>
                    <option value="price_desc" {{ $sortKey === 'price_desc' ? 'selected' : '' }}>Precio (Mayor a menor)</option>
                    <option value="price_asc" {{ $sortKey === 'price_asc' ? 'selected' : '' }}>Precio (Menor a mayor)</option>
                    <option value="stock_desc" {{ $sortKey === 'stock_desc' ? 'selected' : '' }}>Existencias (Mayor a menor)</option>
                    <option value="stock_asc" {{ $sortKey === 'stock_asc' ? 'selected' : '' }}>Existencias (Menor a mayor)</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="per_page" class="form-label">Por página</label>
                <select name="per_page" id="per_page" class="form-select">
                    @foreach ([10, 15, 25, 50] as $size)
                        <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-dark">Aplicar filtros</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de Productos --}}
<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Existencias</th>
                    <th>Estado</th>
                    <th>Fecha de registro</th>
                    @if (auth()->user()->role === 'admin')
                        <th class="text-end">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td class="fw-semibold">{{ $product->name }}</td>
                        <td>{{ $product->description }}</td>
                        <td>{{ $product->category?->name ?? 'Sin categoría' }}</td>
                        <td>${{ number_format((float) $product->price, 2) }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>
                            <x-product-status-badge :status="$product->status" />
                        </td>
                        <td>{{ $product->created_at?->format('d/m/Y H:i') }}</td>
                        @if (auth()->user()->role === 'admin')
                            <td class="text-end text-nowrap">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Seguro que deseas eliminar {{ $product->name }}?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? 9 : 8 }}" class="text-center text-muted py-4">
                            No se encontraron productos con los criterios seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($products->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection