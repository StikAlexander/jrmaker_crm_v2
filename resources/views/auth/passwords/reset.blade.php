<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Enlace a tu CSS si es necesario -->
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="text-center bg-white p-8 rounded-lg shadow-lg">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-24 mx-auto mb-4"> <!-- Logo de la empresa -->
            <h1 class="text-2xl font-bold">Restablecer Contraseña</h1>

            <form method="POST" action="{{ route('password.change.reset') }}" class="mt-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('email')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                    <input id="password" type="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('password')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password-confirm" class="block text-sm font-medium text-gray-700">Confirmar Nueva Contraseña</label>
                    <input id="password-confirm" type="password" name="password_confirmation" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <div class="text-right">
                    <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded-md shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Restablecer Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
