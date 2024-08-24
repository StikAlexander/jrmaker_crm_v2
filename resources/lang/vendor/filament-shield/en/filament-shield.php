<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Columnas de la Tabla
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Nombre',
    'column.guard_name' => 'Nombre del guardia',
    'column.roles' => 'Roles',
    'column.permissions' => 'Permisos',
    'column.updated_at' => 'Actualizado en',

    /*
    |--------------------------------------------------------------------------
    | Campos del Formulario
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Nombre',
    'field.guard_name' => 'Nombre del guardia',
    'field.permissions' => 'Permisos',
    'field.select_all.name' => 'Seleccionar todos los permisos',
    'field.select_all.message' => 'Habilitar todos los Permisos actualmente <span class="text-primary font-medium">Habilitados</span> para este rol',

    /*
    |--------------------------------------------------------------------------
    | Navegación y Recurso
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Acceso',
    'nav.role.label' => 'Roles y Permisos',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'Rol',
    'resource.label.roles' => 'Roles',

    /*
    |--------------------------------------------------------------------------
    | Sección y Pestañas
    |--------------------------------------------------------------------------
    */

    'section' => 'Entidades',
    'resources' => 'Recursos',
    'widgets' => 'Widgets',
    'pages' => 'Páginas',
    'custom' => 'Permisos Personalizados',

    /*
    |--------------------------------------------------------------------------
    | Mensajes
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'No tienes permiso para acceder',

    /*
    |--------------------------------------------------------------------------
    | Etiquetas de Permisos de Recurso
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Ver',
        'view_any' => 'Ver Cualquiera',
        'create' => 'Crear',
        'update' => 'Actualizar',
        'delete' => 'Eliminar',
        'delete_any' => 'Eliminar Cualquiera',
        'force_delete' => 'Eliminar Permanentemente',
        'force_delete_any' => 'Eliminar Permanentemente Cualquiera',
        'restore' => 'Restaurar',
        'reorder' => 'Reordenar',
        'restore_any' => 'Restaurar Cualquiera',
        'replicate' => 'Replicar',
    ],
];
