@extends('filament::layouts.base')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('{{ asset('storage/images/fondo-login-client.jpg') }}')">
        <div class="bg-white p-10 rounded-lg shadow-lg max-w-md w-full">
            <!-- Formulario -->
            {{ $this->form }}
        </div>
    </div>
@endsection
