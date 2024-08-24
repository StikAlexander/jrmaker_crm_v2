<?php
// Cambia las credenciales según tu configuración
$host = 'sandbox.smtp.mailtrap.io';
$port = 2525;
$username = 'ab024018081ad1';
$password = 'dd06d3b89754f3';

// Crear un socket para conectarse al servidor SMTP
$connection = fsockopen($host, $port);

if ($connection) {
    echo 'Conexión exitosa a ' . $host . ':' . $port . "\n";

    // Leer la respuesta inicial del servidor
    $response = fread($connection, 512);
    echo 'Respuesta: ' . $response . "\n";

    // Enviar comando EHLO
    fwrite($connection, "EHLO localhost\r\n");
    $response = fread($connection, 512);
    echo 'Respuesta: ' . $response . "\n";

    // Enviar comando AUTH LOGIN
    fwrite($connection, "AUTH LOGIN\r\n");
    $response = fread($connection, 512);
    echo 'Respuesta: ' . $response . "\n";

    // Enviar nombre de usuario codificado en base64
    fwrite($connection, base64_encode($username) . "\r\n");
    $response = fread($connection, 512);
    echo 'Respuesta: ' . $response . "\n";

    // Enviar contraseña codificada en base64
    fwrite($connection, base64_encode($password) . "\r\n");
    $response = fread($connection, 512);
    echo 'Respuesta: ' . $response . "\n";

    // Verificar si la autenticación fue exitosa
    if (strpos($response, '235') !== false) {
        echo "Autenticación exitosa\n";
    } else {
        echo "Error en la autenticación: " . $response . "\n";
    }

    fclose($connection);
} else {
    echo 'Error al conectar a ' . $host . ':' . $port;
}
?>