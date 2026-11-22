<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Código</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-sm p-4 text-center" style="width: 100%; max-width: 400px;">
        <h4 class="fw-bold mb-2">Código 2FA</h4>
        <p class="text-muted small mb-4">Ingresa el código de 6 dígitos enviado a tu correo.</p>

        <form method="POST" action="{{ route('two-factor.verify') }}" novalidate>
            @csrf

            <div class="mb-3">
                <input type="text" 
                       name="code" 
                       maxlength="6" 
                       class="form-control text-center fs-4 letter-spacing @error('code') is-invalid @enderror" 
                       placeholder="123456" 
                       autofocus>
                @error('code')
                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-dark w-100">Verificar Código</button>
        </form>
    </div>
</body>
</html>