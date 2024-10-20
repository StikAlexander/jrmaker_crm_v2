<x-filament-panels::page.simple>
    <div class="min-h-screen flex items-center justify-center" style="background-image: url('{{ asset('storage/images/fondo-login-client.jpg') }}'); background-size: cover; background-position: center;">
        <div class="bg-white p-10 rounded-lg shadow-lg max-w-md w-full">
            <!-- Título del login -->
            <h2 class="text-center text-2xl font-bold text-red-700 mb-6">{{ __('Beta Clientes J.R. Maker') }}</h2>

            <!-- Formulario -->
            {{ $this->form }}

            <!-- Botón de Ingreso -->
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mt-6">
                {{ __('Ingresar') }}
            </button>
        </div>
    </div>
</x-filament-panels::page.simple>
