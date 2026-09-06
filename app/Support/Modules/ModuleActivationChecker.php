<?php

namespace App\Support\Modules;

/**
 * Puerto para saber si un módulo está activo para la empresa actual.
 * Stub hasta que exista Module/CompanyModules (Admin).
 */
interface ModuleActivationChecker
{
    public function isActive(string $module): bool;
}
