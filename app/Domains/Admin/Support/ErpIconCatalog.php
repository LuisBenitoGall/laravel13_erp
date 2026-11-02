<?php

namespace App\Domains\Admin\Support;

/**
 * Nombres del catálogo curado declarado en resources/js/lib/menuIcons.ts.
 */
final class ErpIconCatalog
{
    /** @var list<string>|null */
    private static ?array $names = null;

    /** @return list<string> */
    public static function names(): array
    {
        if (self::$names !== null) {
            return self::$names;
        }

        $source = (string) file_get_contents(resource_path('js/lib/menuIcons.ts'));

        if (! preg_match('/export const ERP_ICON_NAMES = \[(.*?)\] as const;/s', $source, $block)) {
            return self::$names = [];
        }

        preg_match_all("/'([a-z0-9-]+)'/", $block[1], $found);

        return self::$names = $found[1];
    }
}
