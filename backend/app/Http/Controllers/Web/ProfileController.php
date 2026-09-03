<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\UpdatePasswordRequest;
use App\Http\Requests\Web\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return redirect()
            ->route('web.profile.edit')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => $request->string('password')->toString(),
        ]);

        Auth::logoutOtherDevices($request->string('password')->toString());

        return redirect()
            ->route('web.profile.edit')
            ->with('password_success', 'Contrasena actualizada correctamente.');
    }

    public function updateLocation(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $user = $request->user();
        $user->update([
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Ubicación actualizada',
        ]);
    }
}
