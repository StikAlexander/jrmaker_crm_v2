{{-- resources/views/errors/403.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            text-align: center;
            padding: 50px;
        }
    </style>
</head>
<body>
    <h1>403 - Acceso no autorizado</h1>
    <p>Lo sentimos, pero no tiene acceso a esta sección debido a las restricciones de su tipo de rol.</p>
    <a href="{{ url('/') }}">Volver al inicio</a>
</body>
</html>
