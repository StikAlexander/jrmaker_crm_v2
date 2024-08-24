<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Exitosa</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Enlace a tu CSS si es necesario -->
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="text-center bg-white p-8 rounded-lg shadow-lg">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-24 mx-auto mb-4"> <!-- Logo de la empresa -->
            <h1 class="text-2xl font-bold">¡Bienvenido!</h1>
            <p class="mt-2">Tu cuenta ha sido verificada con éxito.</p>
            <p class="mt-4">Ahora puedes <a href="{{ route('login') }}" class="text-blue-500">iniciar sesión</a>.</p>
        </div>
    </div>
</body>
</html>
