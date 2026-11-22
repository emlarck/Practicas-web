<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
 public function index(Request $request, User $user)
    {
        // Si el usuario ya completó el 2FA, lo mandamos directo al inicio
        if (session('two_factor_verified')) {
            return redirect()->route('inicio');
        }

        return view('auth.two-factor', compact('user'));
    }

// En el método verify():
   public function verify(Request $request)
{
    $request->validate([
        'code' => ['required', 'numeric', 'digits:6'],
    ], [
        'code.required' => 'Debes ingresar el código de verificación.',
        'code.numeric' => 'El código debe contener únicamente números.',
        'code.digits' => 'El código debe ser exactamente de 6 dígitos.',
    ]);

    $storedCode = session('two_factor_code');

    if ($request->code == $storedCode) {
        session()->forget('two_factor_code');
        session(['two_factor_verified' => true]);

return redirect()->route('inicio');    }

    return back()->withErrors([
        'code' => 'El código ingresado es incorrecto o no coincide.',
    ]);
}
}