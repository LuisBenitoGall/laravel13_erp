<?php

namespace App\Support\Modules;

/**
 * Implementación stub: todos los módulos activos (hasta Admin).
 */
final class AlwaysActiveModuleChecker implements ModuleActivationChecker
{
    public function isActive(string $module): bool
    {
        return true;
    }
}
