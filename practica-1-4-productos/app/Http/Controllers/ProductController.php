<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const ALLOWED_PER_PAGE = [10, 15, 25, 50];

    private const SORT_OPTIONS = [
        'name_asc'       => ['name', 'asc'],
        'name_desc'      => ['name', 'desc'],
        'price_desc'     => ['price', 'desc'],
        'price_asc'      => ['price', 'asc'],
        'stock_desc'     => ['stock', 'desc'],
        'stock_asc'      => ['stock', 'asc'],
        'created_desc'   => ['created_at', 'desc'],
        'created_asc'    => ['created_at', 'asc'],
    ];

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
            $perPage = 10;
        }

        $sortKey = $request->input('sort', 'created_desc');
        if (!array_key_exists($sortKey, self::SORT_OPTIONS)) {
            $sortKey = 'created_desc';
        }
        [$sortColumn, $sortDirection] = self::SORT_OPTIONS[$sortKey];

        $query = Product::with('category');

        // Filtro por nombre
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        // Filtro por categoría
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filtro por precio mínimo
        if ($request->filled('min_price') && is_numeric($request->input('min_price'))) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        // Filtro por precio máximo
        if ($request->filled('max_price') && is_numeric($request->input('max_price'))) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Filtro por estado
        if ($request->filled('status') && in_array($request->input('status'), ['Disponible', 'Agotado'], true)) {
            $query->where('status', $request->input('status'));
        }

        $products = $query
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact(
            'products',
            'categories',
            'perPage',
            'sortKey'
        ));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}