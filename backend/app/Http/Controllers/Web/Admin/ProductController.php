<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\ProductsExport;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with(['provider']);

        // Búsqueda por nombre
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filtro por proveedor
        if ($provider_id = $request->get('provider_id')) {
            $query->where('provider_id', $provider_id);
        }

        // Filtro por categoría
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // Filtro por rango de precio
        if ($min_price = $request->get('min_price')) {
            $query->where('price', '>=', (float) $min_price);
        }
        if ($max_price = $request->get('max_price')) {
            $query->where('price', '<=', (float) $max_price);
        }

        // Filtro por disponibilidad
        if ($availability = $request->get('availability')) {
            if ($availability === 'available') {
                $query->where('stock', '>', 0);
            } elseif ($availability === 'low') {
                $query->where('stock', '>', 0)
                    ->where('stock', '<=', 5);
            } elseif ($availability === 'out') {
                $query->where('stock', '=', 0);
            }
        }

        $products = $query->latest()->paginate(20);

        // Obtener opciones para filtros
        $providers = Provider::orderBy('name')->get();
        $categories = Product::distinct()->pluck('category')->sort()->values();

        return view('admin.products.index', compact('products', 'providers', 'categories'));
    }

    public function export()
    {
        return Excel::download(new ProductsExport(), 'productos_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }

    public function create(): View
    {
        $providers = Provider::orderBy('name')->get();

        return view('admin.products.create', compact('providers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'name' => 'required|string|max:255|unique:products',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'min_weight_kg' => 'nullable|numeric|min:0',
            'max_weight_kg' => 'nullable|numeric|min:0',
            'recommended_breeds' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Procesar foto si existe
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('products', 'public');
            $validated['photo'] = $path;
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(Product $product): View
    {
        $product->load(['provider']);

        $product->recommended_breeds_string = $product->recommended_breeds
            ? implode(', ', $product->recommended_breeds)
            : 'Todas';

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $providers = Provider::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'providers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'min_weight_kg' => 'nullable|numeric|min:0',
            'max_weight_kg' => 'nullable|numeric|min:0',
            'recommended_breeds' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Procesar foto si existe
        if ($request->hasFile('photo')) {
            // Eliminar foto anterior si existe
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $path = $request->file('photo')->store('products', 'public');
            $validated['photo'] = $path;
        }

        $product->update($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Eliminar foto si existe
        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            Storage::disk('public')->delete($product->photo);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto eliminado exitosamente');
    }
}
