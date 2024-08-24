<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class EncryptDocumentNumbers extends Command
{
    protected $signature = 'encrypt:document-numbers';
    protected $description = 'Encripta los números de documento de los usuarios existentes';

    public function handle()
    {
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                // Si el número de documento no está encriptado (evitamos re-encriptar)
                if ($this->isDecrypted($user->document_number)) {
                    $user->document_number = Crypt::encryptString($user->getOriginal('document_number'));
                    $user->save();
                }
            }
        });

        $this->info('Números de documento encriptados correctamente.');
    }

    private function isDecrypted($value)
    {
        try {
            // Intenta desencriptar para comprobar si ya está encriptado
            Crypt::decryptString($value);
            return false;
        } catch (\Exception $e) {
            return true;
        }
    }
}
