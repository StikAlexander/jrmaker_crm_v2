<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.brand_name', 'J.R. MAKER S.A.S.');
        $this->migrator->add('general.brand_logo', 'sites/jr_logo_completo.png');
        $this->migrator->add('general.brand_logoHeight', '4rem');
        $this->migrator->add('general.site_active', true);
        $this->migrator->add('general.site_favicon', 'sites/jr_favi.ico');
        $this->migrator->add('general.site_theme', [
            "primary" => "rgb(19, 83, 196)",
            "secondary" => "rgb(255, 137, 84)",
            "gray" => "rgb(107, 114, 128)",
            "success" => "rgb(12, 195, 178)",
            "danger" => "rgb(199, 29, 81)",
            "info" => "rgb(113, 12, 195)",
            "warning" => "rgb(255, 186, 93)",
        ]);
    }
};
