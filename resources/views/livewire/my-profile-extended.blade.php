<div class="space-y-6 divide-y divide-gray-900/10 dark:divide-white/10">

    <!-- Sección de Información Personal -->
    <x-filament-breezy::grid-section md=2 :title="__('filament-breezy::default.profile.personal_info.heading')" :description="__('filament-breezy::default.profile.personal_info.subheading')">
        <x-filament::card>
            <form wire:submit.prevent="submit" class="space-y-6">
                {{ $this->form }}
                <div class="text-right">
                    <x-filament::button type="submit" class="align-right">
                        Modificar
                    </x-filament::button>
                </div>
            </form>
        </x-filament::card>
    </x-filament-breezy::grid-section>

    <!-- Elimina la Sección de Cambio de Contraseña -->
    
</div>
