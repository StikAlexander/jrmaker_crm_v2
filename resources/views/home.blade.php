@extends('layouts.base')

@section('title', 'Bienvenido a JR Maker CRM')

@section('content')
    <h1>Bienvenido a JR Maker CRM</h1>
    <p>Seleccione el panel al que desea acceder:</p>

    <a href="{{ url('/client') }}" class="button">Pagos Clientes</a>
    <a href="{{ url('/admin') }}" class="button">Equipo JR Maker</a>
@endsection
