<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Almacenamos la configuración del correo
        $this->migrator->add('mail.from_address', 'operaciones@jrmaker.com.co');
        $this->migrator->add('mail.from_name', 'J.R. MAKER S.A.S.');
        $this->migrator->add('mail.driver', 'smtp');
        
        // Cambia a Hostinger como servicio de correo
        $this->migrator->add('mail.host', 'smtp.hostinger.com');
        $this->migrator->add('mail.port', 465);
        $this->migrator->add('mail.encryption', 'ssl');
        
        // Ciframos las credenciales sensibles
        $this->migrator->addEncrypted('mail.username', 'operaciones@jrmaker.com.co');
        $this->migrator->addEncrypted('mail.password', 'Metroidvania22.');
        
        $this->migrator->add('mail.timeout', null);
        $this->migrator->add('mail.local_domain', null);
    }
};
