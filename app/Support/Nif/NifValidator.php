<?php

namespace App\Support\Nif;

/**
 * Validación de NIF/NIE/CIF españoles y EIN EE.UU.
 * Puerto del algoritmo v1 AppServiceProvider::validateNif (sin session companySettings).
 */
final class NifValidator
{
    /**
     * Vacío = válido (caller decide required). No vacío = formato + dígito de control.
     */
    public static function isValid(?string $nif): bool
    {
        if ($nif === null || $nif === '') {
            return true;
        }

        $nif = strtoupper($nif);
        $num = [];
        for ($i = 0; $i < 9; $i++) {
            $num[$i] = substr($nif, $i, 1);
        }

        if (! preg_match('/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$|^[0-9]{2}-[0-9]{7}$)/', $nif)) {
            return false;
        }

        if (preg_match('/(^[0-9]{8}[A-Z]{1}$)/', $nif)) {
            return $num[8] === substr('TRWAGMYFPDXBNJZSQVHLCKE', ((int) substr($nif, 0, 8)) % 23, 1);
        }

        if (preg_match('/^[XYZ]{1}/', $nif)) {
            $normalized = str_replace(['X', 'Y', 'Z'], ['0', '1', '2'], $nif);

            return $num[8] === substr('TRWAGMYFPDXBNJZSQVHLCKE', ((int) substr($normalized, 0, 8)) % 23, 1);
        }

        if (preg_match('/^[0-9]{2}-[0-9]{7}/', $nif)) {
            return true;
        }

        $cifCodes = 'JABCDEFGHI';
        $sum = (string) self::cifSum($nif);
        $n = (10 - (int) substr($sum, -1)) % 10;

        if (preg_match('/^[ABCDEFGHJNPQRSUVW]{1}/', $nif)) {
            if (in_array($nif[0], ['A', 'B', 'E', 'H'], true)) {
                return $nif[8] === (string) $n;
            }

            if (in_array($nif[0], ['K', 'P', 'Q', 'S'], true)) {
                return $nif[8] === $cifCodes[$n];
            }

            if (is_numeric($nif[8])) {
                return $nif[8] === (string) $n;
            }

            return $nif[8] === $cifCodes[$n];
        }

        return false;
    }

    private static function cifSum(string $cif): int
    {
        $sum = (int) $cif[2] + (int) $cif[4] + (int) $cif[6];

        for ($i = 1; $i < 8; $i += 2) {
            $tmp = (string) (2 * (int) $cif[$i]);
            $sum += (int) $tmp[0] + ((strlen($tmp) === 2) ? (int) $tmp[1] : 0);
        }

        return $sum;
    }
}
