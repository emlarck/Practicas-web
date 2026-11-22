<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
        <h4 class="text-center mb-3 fw-bold">Iniciar Sesión</h4>

        <form method="POST" action="{{ url('/login') }}" novalidate>
            @csrf

            <!-- Correo Electrónico -->
            <div class="mb-3">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" 
                       name="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email') }}" 
                       placeholder="ejemplo@correo.com" 
                       autofocus>
                @error('email')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" 
                       name="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-dark w-100 mb-3">Ingresar</button>

            <div class="text-center mt-3">
                <p class="mb-1 text-muted small">¿No tienes una cuenta?</p>
                <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-sm w-100">
                    Registrarse 
                </a>
            </div>
        </form>
    </div>
</body>
</html>