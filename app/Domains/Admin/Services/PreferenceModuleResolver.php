<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Support\MenuFunctionalityHref;
use App\Domains\Admin\Support\PreferenceUrlNormalizer;

/**
 * Longest prefix match path → module_id vía hrefs de Functionalities (D3).
 * Hrefs: {@see MenuFunctionalityHref} (misma fuente que MenuBuilder).
 */
class PreferenceModuleResolver
{
    public function resolve(string $path): ?int
    {
        $normalized = PreferenceUrlNormalizer::normalize($path);
        if (! PreferenceUrlNormalizer::isAllowedAppPath($normalized)) {
            return null;
        }

        $pathOnly = explode('?', $normalized, 2)[0];
        $pathOnly = rtrim($pathOnly, '/') ?: '/';

        $bestModuleId = null;
        $bestLen = -1;

        foreach ($this->hrefModuleMap() as $href => $moduleId) {
            if ($href === '#' || $href === '') {
                continue;
            }

            $normalizedHref = rtrim($href, '/') ?: '/';

            if ($pathOnly === $normalizedHref || str_starts_with($pathOnly, $normalizedHref.'/')) {
                $len = strlen($normalizedHref);
                if ($len > $bestLen) {
                    $bestLen = $len;
                    $bestModuleId = $moduleId;
                }
            }
        }

        return $bestModuleId;
    }

    /** @return array<string, int> */
    private function hrefModuleMap(): array
    {
        $map = [];

        $dashboardId = Module::query()->where('slug', 'dashboard')->value('id');
        if (is_int($dashboardId) || is_numeric($dashboardId)) {
            $map['/dashboard'] = (int) $dashboardId;
        }

        $functionalities = Functionality::query()
            ->get(['slug', 'module_id']);

        foreach ($functionalities as $functionality) {
            $href = MenuFunctionalityHref::for((string) $functionality->slug);
            $moduleId = $functionality->module_id;
            if ($href === '#' || $moduleId < 1) {
                continue;
            }

            $map[$href] = $moduleId;
        }

        return $map;
    }
}
