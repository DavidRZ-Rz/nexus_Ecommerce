<!DOCTYPE html>
<html>
<head>
    <title>Restablecimiento de Contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #d30000; padding: 10px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .code { 
            font-size: 24px; 
            font-weight: bold; 
            color: #d30000;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #fff;
            border: 1px dashed #ccc;
        }
        .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>NEXUS ECOMMERCE</h1>
        </div>
        
        <div class="content">
            <p>Hola,</p>
            <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
            <p>Utiliza el siguiente código para completar el proceso:</p>
            
            <div class="code">{{ $code }}</div>
            
            <p>Este código expirará en 60 minutos. Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
            
            <p>Gracias,<br>El equipo de Nexus Ecommerce</p>
        </div>
        
        <div class="footer">
            © {{ date('Y') }} Nexus Ecommerce. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>