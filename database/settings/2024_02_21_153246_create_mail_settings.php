<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Almacenamos la configuración del correo
        $this->migrator->add('mail.from_address', 'makercolombia@hotmail.com');
        $this->migrator->add('mail.from_name', 'J.R. MAKER S.A.S.');
        $this->migrator->add('mail.driver', 'smtp');
        
        // Cambia a Outlook como servicio de correo
        $this->migrator->add('mail.host', 'smtp-mail.outlook.com');
        $this->migrator->add('mail.port', 587);
        $this->migrator->add('mail.encryption', 'starttls');
        
        // Ciframos las credenciales sensibles
        $this->migrator->addEncrypted('mail.username', 'makercolombia@hotmail.com');
        $this->migrator->addEncrypted('mail.password', 'vhyewhclplcazmao');
        
        $this->migrator->add('mail.timeout', null);
        $this->migrator->add('mail.local_domain', null);
    }
};
