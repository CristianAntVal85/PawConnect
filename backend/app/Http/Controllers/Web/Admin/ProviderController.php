<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProviderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Provider::query();

        // Búsqueda por nombre o email
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        }

        $providers = $query->withCount('products')
            ->latest()
            ->paginate(20);

        return view('admin.providers.index', compact('providers'));
    }

    public function create(): View
    {
        return view('admin.providers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:providers',
            'email' => 'required|email|unique:providers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Provider::create($validated);

        return redirect()->route('admin.providers.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function show(Provider $provider): View
    {
        $provider->load(['products']);
        $products = $provider->products()->paginate(10);

        return view('admin.providers.show', compact('provider', 'products'));
    }

    public function edit(Provider $provider): View
    {
        return view('admin.providers.edit', compact('provider'));
    }

    public function update(Request $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:providers,name,' . $provider->id,
            'email' => 'required|email|unique:providers,email,' . $provider->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $provider->update($validated);

        return redirect()->route('admin.providers.show', $provider)
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        // Verificar que no tenga productos asociados
        if ($provider->products()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un proveedor que tiene productos asociados');
        }

        $provider->delete();

        return redirect()->route('admin.providers.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
