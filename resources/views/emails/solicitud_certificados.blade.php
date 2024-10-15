<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Certificado de Retenciones</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            max-width: 150px;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('storage/images/jr_maker_logo.png') }}" alt="Logo de J.R. Maker S.A.S.">
        </div>

        <p>Buen día, estimado cliente,</p>

        <p>Me permito dirigirme a usted para solicitar de manera cordial el certificado de retenciones correspondiente a las facturas emitidas a su nombre durante el año {{ date('Y') }}. Según nuestros registros, a dichas facturas se les han aplicado retenciones, y requerimos el certificado que lo respalde para nuestros archivos contables.</p>

        <p>Para facilitar la identificación en su sistema, nuestra empresa es <strong>J.R. Maker S.A.S.</strong>, con <strong>NIT 901.622.321-7</strong>. Agradecemos que la información sea enviada a la dirección de correo electrónico: <a href="mailto:makercolombia@hotmail.com">makercolombia@hotmail.com</a>.</p>

        <p>En caso de que, según su información, no se hayan realizado retenciones a nuestra empresa, puede omitir este correo.</p>

        <p>Si tiene alguna duda o inquietud, estoy disponible para atenderlo. Puede comunicarse conmigo, <strong>Stik Alexander Gamboa</strong>, representante legal, al número <strong>310 329 2291</strong>, o, si lo prefiere, también puede contactar a <strong>John Gamboa</strong> al mismo número.</p>

        <p>Agradecemos su atención y pronta respuesta.</p>

        <p>Atentamente,</p>
        <p><strong>Stik Alexander Gamboa</strong><br>
        Representante Legal<br>
        J.R. Maker S.A.S.</p>

        <div class="footer">
            <p>© {{ date('Y') }} J.R. Maker S.A.S. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
