<?php
$rawPayload = json_encode([
    'data' => [
        'id' => '123456',
        'status' => 'approved',
        'external_reference' => 'ref_66dcec56d8bc3'
    ]
], JSON_UNESCAPED_SLASHES);

echo 'Contenido del payload (script generación): ' . $rawPayload . PHP_EOL;

$secretKey = 'e9679d2b72d81cf9abb1fae015f23a31fc0b70d87694467298a2781cd8973651';
$signature = hash_hmac('sha256', $rawPayload, $secretKey);
echo $signature;
