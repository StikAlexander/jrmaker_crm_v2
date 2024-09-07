setTimeout(function() {
    window.location.href = "{{ $paymentLink }}";  // Redirige al enlace de pago después de unos segundos
}, 5000);  // Espera 5 segundos antes de redirigir
