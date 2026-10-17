<?php

declare(strict_types=1);

/**
 * One-shot migration: hierarchical EN keys → castellano snake_case.
 * Usage: php scripts/migrate-i18n-keys.php
 */
$root = dirname(__DIR__);

/** @var array<string, string> $map old → new */
$map = [
    // Generics
    'admin.functionalities.create' => 'crear',
    'admin.functionalities.form.name' => 'nombre',
    'admin.functionalities.form.save' => 'guardar',
    'admin.functionalities.form.slug' => 'slug',
    'admin.functionalities.form.status' => 'estado',
    'admin.functionalities.form.module' => 'modulo',
    'admin.modules.column.label' => 'etiqueta',
    'admin.modules.column.level' => 'nivel',
    'admin.modules.column.name' => 'nombre',
    'admin.modules.column.slug' => 'slug',
    'admin.modules.column.status' => 'estado',
    'admin.modules.create' => 'crear',
    'admin.modules.form.color' => 'color',
    'admin.modules.form.icon' => 'icono',
    'admin.modules.form.level' => 'nivel',
    'admin.modules.form.name' => 'nombre',
    'admin.modules.form.save' => 'guardar',
    'admin.modules.form.slug' => 'slug',
    'admin.modules.form.status' => 'estado',
    'admin.company_module_authorizations.authorize' => 'autorizar',
    'admin.company_module_authorizations.company' => 'empresa',
    'admin.company_module_authorizations.revoke' => 'revocar',
    'admin.company_modules.turn_off' => 'apagar',
    'admin.company_modules.turn_on' => 'encender',
    'auth.login.password' => 'contrasena',
    'auth.login.title' => 'entrar',
    'auth.onboard.name' => 'nombre',
    'auth.onboard.nif' => 'nif',
    'auth.onboard.tradename' => 'nombre_comercial',
    'auth.register.continue' => 'continuar',
    'auth.register.email' => 'email',
    'auth.register.login' => 'entrar',
    'auth.register.name' => 'nombre',
    'auth.register.password' => 'contrasena',
    'auth.register.password_confirmation' => 'confirmar_contrasena',
    'auth.register.surname' => 'apellidos',
    'auth.reset.confirm' => 'confirmar',
    'auth.reset.email' => 'email',
    'auth.reset.password' => 'contrasena',
    'auth.reset.submit' => 'guardar',
    'auth.select_company.company' => 'empresa',
    'auth.select_company.continue' => 'continuar',
    'common.create' => 'crear',
    'common.email' => 'email',
    'common.language' => 'idioma',
    'common.logout' => 'salir',
    'common.save' => 'guardar',
    'partners.companies.column.name' => 'nombre',
    'partners.companies.column.nif' => 'nif',
    'partners.companies.column.slug' => 'slug',
    'partners.companies.column.status' => 'estado',
    'partners.companies.column.tradename' => 'nombre_comercial',
    'partners.companies.create' => 'crear',
    'partners.companies.form.name' => 'nombre',
    'partners.companies.form.nif' => 'nif',
    'partners.companies.form.save' => 'guardar',
    'partners.companies.form.slug' => 'slug',
    'partners.companies.form.status' => 'estado',
    'partners.companies.form.tradename' => 'nombre_comercial',
    'partners.companies.members.action' => 'accion',
    'partners.companies.members.email' => 'email',
    'partners.companies.members.name' => 'nombre',
    'partners.companies.members.role' => 'rol',

    // Specific / families
    'admin.company_module_authorizations.authorized' => 'modulo_autorizado',
    'admin.company_module_authorizations.revoked' => 'autorizacion_revocada',
    'admin.company_module_authorizations.title' => 'autorizacion_de_modulos',
    'admin.company_modules.disabled' => 'modulo_apagado',
    'admin.company_modules.enabled' => 'modulo_encendido',
    'admin.company_modules.for_company' => 'modulos_empresa_para',
    'admin.company_modules.not_authorized' => 'no_autorizado',
    'admin.company_modules.requires_authorization' => 'requiere_autorizacion',
    'admin.company_modules.subtitle' => 'modulos_empresa_subtitulo',
    'admin.company_modules.title' => 'modulos_empresa',
    'admin.functionalities.create_title' => 'funcionalidad_crear',
    'admin.functionalities.created' => 'funcionalidad_creada',
    'admin.functionalities.deleted' => 'funcionalidad_eliminada',
    'admin.functionalities.edit_title' => 'funcionalidad_editar',
    'admin.functionalities.form.label_es' => 'etiqueta_es',
    'admin.functionalities.form.status_active' => 'estado_active',
    'admin.functionalities.form.status_inactive' => 'estado_inactive',
    'admin.functionalities.slug_immutable' => 'slug_inmutable',
    'admin.functionalities.subtitle' => 'funcionalidades_subtitulo',
    'admin.functionalities.title' => 'funcionalidades',
    'admin.functionalities.updated' => 'funcionalidad_actualizada',
    'admin.modules.create_title' => 'modulo_crear',
    'admin.modules.created' => 'modulo_creado',
    'admin.modules.deleted' => 'modulo_eliminado',
    'admin.modules.edit_title' => 'modulo_editar',
    'admin.modules.form.label_en' => 'etiqueta_en',
    'admin.modules.form.label_es' => 'etiqueta_es',
    'admin.modules.form.level_admin_only' => 'nivel_admin_only',
    'admin.modules.form.level_basic' => 'nivel_basic',
    'admin.modules.form.level_optional' => 'nivel_optional',
    'admin.modules.form.status_active' => 'estado_active',
    'admin.modules.form.status_inactive' => 'estado_inactive',
    'admin.modules.subtitle' => 'modulos_subtitulo',
    'admin.modules.title' => 'modulos',
    'admin.modules.updated' => 'modulo_actualizado',
    'auth.company_forbidden' => 'empresa_acceso_prohibido',
    'auth.company_switched' => 'empresa_cambiada',
    'auth.failed' => 'credenciales_incorrectas',
    'auth.forgot.back_to_login' => 'volver_al_login',
    'auth.forgot.submit' => 'enviar_enlace',
    'auth.forgot.subtitle' => 'recuperar_contrasena_subtitulo',
    'auth.forgot.title' => 'recuperar_contrasena',
    'auth.login.access' => 'acceder',
    'auth.login.forgot_password' => 'olvidaste_contrasena',
    'auth.login.hide_password' => 'ocultar_contrasena',
    'auth.login.no_account' => 'no_tienes_cuenta',
    'auth.login.register' => 'registrate',
    'auth.login.remember' => 'recordarme',
    'auth.login.show_password' => 'mostrar_contrasena',
    'auth.login.subtitle' => 'login_subtitulo',
    'auth.no_company' => 'sin_empresa_activa',
    'auth.onboard.create_and_enter' => 'crear_empresa_y_entrar',
    'auth.onboard.logo' => 'logo_opcional',
    'auth.onboard.subtitle' => 'onboarding_empresa_subtitulo',
    'auth.onboard.title' => 'tu_empresa',
    'auth.register.accept_and' => 'y_los',
    'auth.register.accept_prefix' => 'acepto_el',
    'auth.register.has_account' => 'ya_tienes_cuenta',
    'auth.register.legal_notice' => 'aviso_legal_enlace',
    'auth.register.subtitle' => 'registro_subtitulo',
    'auth.register.terms' => 'terminos',
    'auth.register.title' => 'crear_cuenta',
    'auth.reset.title' => 'nueva_contrasena',
    'auth.select_company.subtitle' => 'seleccionar_empresa_subtitulo',
    'auth.select_company.title' => 'seleccionar_empresa',
    'dashboard.subtitle' => 'panel_subtitulo',
    'dashboard.title' => 'inicio',
    'legal.back_to_register' => 'volver_al_registro',
    'legal.notice.body' => 'aviso_legal_cuerpo',
    'legal.notice.title' => 'aviso_legal',
    'legal.terms.body' => 'terminos_cuerpo',
    'legal.terms.title' => 'terminos_y_condiciones',
    'partners.companies.created' => 'empresa_creada',
    'partners.companies.deleted' => 'empresa_eliminada',
    'partners.companies.edit' => 'empresa_editar',
    'partners.companies.form.logo' => 'logo_url',
    'partners.companies.form.status_active' => 'estado_activa',
    'partners.companies.form.status_closed' => 'estado_cerrada',
    'partners.companies.form.status_draft' => 'estado_borrador',
    'partners.companies.manage_members' => 'gestionar_miembros_y_roles',
    'partners.companies.members.back' => 'volver_a_empresa',
    'partners.companies.members.choose' => 'elegir',
    'partners.companies.members.subtitle' => 'miembros_subtitulo',
    'partners.companies.members.title' => 'miembros',
    'partners.companies.new' => 'empresa_nueva',
    'partners.companies.role_assigned' => 'rol_empresa_asignado',
    'partners.companies.role_guest' => 'invitado',
    'partners.companies.role_manager' => 'manager',
    'partners.companies.subtitle' => 'empresas_subtitulo',
    'partners.companies.title' => 'empresas',
    'partners.companies.updated' => 'empresa_actualizada',
    'scratch_global.catalog_items.created' => 'catalog_item_creado',
    'scratch_global.catalog_items.deleted' => 'catalog_item_eliminado',
    'scratch_global.catalog_items.title' => 'catalog_items',
    'scratch_global.catalog_items.updated' => 'catalog_item_actualizado',
    'welcome.subtitle' => 'bienvenida_subtitulo',
    'welcome.title' => 'bienvenida_titulo',
];

foreach ($map as $old => $new) {
    if (! preg_match('/^[a-z0-9_]+$/', $new)) {
        fwrite(STDERR, "Invalid new key: {$new} (from {$old})\n");
        exit(1);
    }
}

$locales = ['es', 'en', 'ca'];
/** @var array<string, array<string, string>> $dicts */
$dicts = [];

foreach ($locales as $locale) {
    $path = $root.'/lang/'.$locale.'.json';
    /** @var array<string, string> $dict */
    $dict = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $dicts[$locale] = $dict;

    $unmapped = array_diff(array_keys($dict), array_keys($map));
    if ($unmapped !== []) {
        fwrite(STDERR, "Unmapped keys in {$locale}: ".implode(', ', $unmapped)."\n");
        exit(1);
    }
}

/** @var array<string, array<string, string>> $newDicts */
$newDicts = ['es' => [], 'en' => [], 'ca' => []];

foreach ($locales as $locale) {
    foreach ($map as $old => $new) {
        if (! array_key_exists($old, $dicts[$locale])) {
            fwrite(STDERR, "Missing old key {$old} in {$locale}\n");
            exit(1);
        }
        $value = $dicts[$locale][$old];
        if (array_key_exists($new, $newDicts[$locale]) && $newDicts[$locale][$new] !== $value) {
            // Prefer shorter / already-set generic from common.* when conflict on EN.
            // Keep first write; warn.
            fwrite(STDERR, "Value conflict for {$new} in {$locale}: keep «{$newDicts[$locale][$new]}», skip «{$value}» (from {$old})\n");

            continue;
        }
        $newDicts[$locale][$new] = $value;
    }
    ksort($newDicts[$locale], SORT_STRING);
}

foreach ($locales as $locale) {
    $path = $root.'/lang/'.$locale.'.json';
    file_put_contents(
        $path,
        json_encode($newDicts[$locale], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n"
    );
    echo "Wrote {$path} (".count($newDicts[$locale])." keys)\n";
}

// Replace in source files (longest old keys first to avoid partial collisions — none with dots vs shorter).
$oldKeys = array_keys($map);
usort($oldKeys, fn ($a, $b) => strlen($b) <=> strlen($a));

$globs = [
    $root.'/resources/js/**/*.tsx',
    $root.'/resources/js/**/*.ts',
    $root.'/app/**/*.php',
    $root.'/tests/**/*.php',
    $root.'/docs/standards/templates-frontend.md',
];

$files = [];
foreach ($globs as $pattern) {
    foreach (glob($pattern) ?: [] as $file) {
        $files[$file] = true;
    }
}
// Recursive for ** if glob fails on Windows without GLOB_BRACE
$iteratorDirs = [
    $root.'/resources/js',
    $root.'/app',
    $root.'/tests',
];
foreach ($iteratorDirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $ext = $file->getExtension();
        if (in_array($ext, ['tsx', 'ts', 'php'], true)) {
            $files[$file->getPathname()] = true;
        }
    }
}
$files[$root.'/docs/standards/templates-frontend.md'] = true;

// Solo sustituir literales dentro de t()/__() — nunca route()/to_route()/name().
$changed = 0;
foreach (array_keys($files) as $file) {
    $content = (string) file_get_contents($file);
    $original = $content;
    foreach ($oldKeys as $old) {
        $new = $map[$old];
        $quotedOld = preg_quote($old, '/');
        $content = preg_replace(
            "/\\b(t|__)\\(\\s*'{$quotedOld}'/",
            "$1('{$new}'",
            $content,
        ) ?? $content;
        $content = preg_replace(
            "/\\b(t|__)\\(\\s*\"{$quotedOld}\"/",
            "$1(\"{$new}\"",
            $content,
        ) ?? $content;
    }
    if ($content !== $original) {
        file_put_contents($file, $content);
        $changed++;
        echo "Updated {$file}\n";
    }
}

$mapPath = $root.'/openspec/changes/c2026-10-16-i18n-key-convention/key-map.json';
file_put_contents($mapPath, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
echo "Wrote map {$mapPath}\n";
echo "Files updated: {$changed}\n";
echo "Done.\n";
