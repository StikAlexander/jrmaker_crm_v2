<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código de Cambio de Contraseña</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Enlace a tu CSS -->
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="text-center bg-white p-8 rounded-lg shadow-lg">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-24 mx-auto mb-4"> <!-- Logo de la empresa -->
            <h1 class="text-2xl font-bold">Verificar Cambio de Contraseña</h1>

            <form method="POST" action="{{ route('password.change.verify_code') }}" class="mt-4">
                @csrf

                <div class="mb-4">
                    <label for="token" class="block text-sm font-medium text-gray-700">Código de Verificación</label>
                    <input type="text" name="token" id="token" class="mt-1 block w-full" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="new_password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                    <input type="password" name="new_password" id="new_password" class="mt-1 block w-full" required>
                </div>

                <div class="mb-4">
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirme la Nueva Contraseña</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="mt-1 block w-full" required>
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Verificar y Cambiar Contraseña</button>
            </form>
        </div>
    </div>
</body>
</html>
