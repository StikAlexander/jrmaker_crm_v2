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
        $this->migrator->addEncrypted('mail.username', 'ab024018081ad1');
        $this->migrator->addEncrypted('mail.password', 'dd06d3b89754f3');
        $this->migrator->add('mail.timeout', null);
        $this->migrator->add('mail.local_domain', null);
    }
};
