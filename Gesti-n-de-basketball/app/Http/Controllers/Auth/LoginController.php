<?php

namespace App\Http\Controllers\Auth; 
// <-- Fíjate que incluya \Auth
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\URL;

class LoginController extends Controller
{
public function store(\Illuminate\Http\Request $request)
{
    // Validaciones personalizadas con mensajes claros en español
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string', 'min:6'],
    ], [
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'Por favor introduce un correo electrónico válido.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
    ]);

    if (\Illuminate\Support\Facades\Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        $request->session()->regenerate();
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->role === 'empleado') {
            session(['two_factor_verified' => true]);
return redirect()->route('inicio');    }
        

      $code = rand(100000, 999999);
session(['two_factor_code' => $code]);

\Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($code));

// Generar una URL firmada que expire en 5 minutos
return redirect()->temporarySignedRoute(
    'two-factor.index', 
    now()->addMinutes(5), 
    ['user' => $user->id]
);
    }

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con ningún registro activo.',
    ])->onlyInput('email');
}
} // <-- Y esta llave al final para cerrar la clase