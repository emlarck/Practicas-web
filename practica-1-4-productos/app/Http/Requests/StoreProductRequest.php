<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:products,name'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price'       => ['required', 'numeric', 'gt:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'status'      => ['required', Rule::in(['Disponible', 'Agotado'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'El nombre del producto es obligatorio.',
            'name.unique'          => 'Ya existe un producto con este nombre.',
            'description.required' => 'La descripción del producto es obligatoria.',
            'category_id.required' => 'Debes seleccionar una categoría.',
            'category_id.exists'   => 'La categoría seleccionada no existe en el catálogo.',
            'price.required'       => 'El precio es obligatorio.',
            'price.numeric'        => 'El precio debe ser un valor numérico.',
            'price.gt'             => 'El precio debe ser mayor que cero.',
            'stock.required'       => 'Las existencias son obligatorias.',
            'stock.integer'        => 'Las existencias deben ser un número entero.',
            'stock.min'            => 'Las existencias deben ser un número igual o mayor a cero.',
            'status.required'      => 'El estado del producto es obligatorio.',
            'status.in'            => 'El estado debe ser Disponible o Agotado.',
        ];
    }
}