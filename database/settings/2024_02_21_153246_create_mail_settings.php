<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.from_address', 'makercolombia@hotmail.com');
        $this->migrator->add('mail.from_name', 'J.R. MAKER S.A.S.');
        $this->migrator->add('mail.driver', 'smtp');
        $this->migrator->add('mail.host', 'sandbox.smtp.mailtrap.io');
        $this->migrator->add('mail.port', 2525);
        $this->migrator->add('mail.encryption', 'tls');
        $this->migrator->addEncrypted('mail.username', 'c34db037ba25a7');
        $this->migrator->addEncrypted('mail.password', '073f8878b00147');
        $this->migrator->add('mail.timeout', null);
        $this->migrator->add('mail.local_domain', null);
    }
};