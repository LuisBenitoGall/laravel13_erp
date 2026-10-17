<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$keys = [
    'cancelar' => ['Cancelar', 'Cancel', 'Cancel·lar'],
    'descripcion' => ['Descripción', 'Description', 'Descripció'],
    'editar' => ['Editar', 'Edit', 'Editar'],
    'eliminar' => ['Eliminar', 'Delete', 'Eliminar'],
    'matriz_permisos' => ['Matriz de permisos', 'Permission matrix', 'Matriu de permisos'],
    'no' => ['No', 'No', 'No'],
    'rol_actualizado' => ['Rol actualizado', 'Role updated', 'Rol actualitzat'],
    'rol_confirmar_eliminar' => ['¿Eliminar este rol de plataforma?', 'Delete this platform role?', 'Eliminar aquest rol de plataforma?'],
    'rol_con_usuarios_no_borrable' => ['No se puede borrar un rol con usuarios asignados', 'Cannot delete a role with assigned users', 'No es pot esborrar un rol amb usuaris assignats'],
    'rol_crear' => ['Crear rol', 'Create role', 'Crear rol'],
    'rol_creado' => ['Rol creado', 'Role created', 'Rol creat'],
    'rol_editar' => ['Editar rol', 'Edit role', 'Editar rol'],
    'rol_eliminado' => ['Rol eliminado', 'Role deleted', 'Rol eliminat'],
    'rol_manager_matriz_solo_lectura' => [
        'Los permisos del manager local se sincronizan con los módulos autorizados; esta plantilla no actúa como techo.',
        'Local manager permissions sync from authorized modules; this template is not a ceiling.',
        'Els permisos del manager local es sincronitzen amb els mòduls autoritzats; aquesta plantilla no actua com a sostre.',
    ],
    'rol_nombre_duplicado' => ['Ya existe un rol de plataforma con ese nombre', 'A platform role with that name already exists', 'Ja existeix un rol de plataforma amb aquest nom'],
    'rol_plantilla_no_borrable' => ['Las plantillas manager y guest no se pueden borrar', 'The manager and guest templates cannot be deleted', 'Les plantilles manager i guest no es poden esborrar'],
    'rol_plantilla_nombre_inmutable' => ['El nombre de esta plantilla no se puede cambiar', 'This template name cannot be changed', 'El nom d\'aquesta plantilla no es pot canviar'],
    'rol_super_admin_inmutable' => ['El rol Super Admin no se puede modificar ni borrar', 'The Super Admin role cannot be modified or deleted', 'El rol Super Admin no es pot modificar ni esborrar'],
    'rol_universal' => ['Universal', 'Universal', 'Universal'],
    'rol_universal_ayuda' => ['Usable por tenants (defaults de empresa)', 'Usable by tenants (company defaults)', 'Utilitzable pels tenants (defaults d\'empresa)'],
    'rol_universal_sin_admin_only' => ['Los módulos AdminOnly no aparecen en roles universales.', 'AdminOnly modules are omitted for universal roles.', 'Els mòduls AdminOnly no apareixen en rols universals.'],
    'roles' => ['Roles', 'Roles', 'Rols'],
    'roles_subtitulo' => ['Roles de plataforma (universales y genéricos).', 'Platform roles (universal and generic).', 'Rols de plataforma (universals i genèrics).'],
    'si' => ['Sí', 'Yes', 'Sí'],
];

foreach (['es', 'en', 'ca'] as $i => $locale) {
    $path = $root.'/lang/'.$locale.'.json';
    /** @var array<string, string> $dict */
    $dict = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    foreach ($keys as $key => $values) {
        $dict[$key] = $values[$i];
    }
    ksort($dict, SORT_STRING);
    file_put_contents($path, json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
    echo "{$locale}: ".count($dict)." keys\n";
}
