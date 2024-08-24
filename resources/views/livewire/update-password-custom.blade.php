<!-- resources/views/livewire/update-password-custom.blade.php -->

<x-filament-breezy::grid-section md=2 :title="__('Actualizar Contraseña')" :description="__('Ingresa tu nueva contraseña y el código de verificación.')">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="button" wire:click="sendVerificationCode" class="align-right">
                    {{ __('Enviar Código de Verificación') }}
                </x-filament::button>
            </div>

            @if ($codeSent)
                <div class="space-y-6">
                    <x-filament::input wire:model="verification_code" type="text" placeholder="Ingrese el código de verificación" />
                    <x-filament::button type="button" wire:click="verifyCode" class="align-right">
                        {{ __('Confirmar Código y Actualizar Contraseña') }}
                    </x-filament::button>
                </div>
            @endif
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>
