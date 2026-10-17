<?php

namespace App\Support\I18n;

use App\Models\User;
use Illuminate\Http\Request;

final class LocaleResolver
{
    public function default(): string
    {
        return (string) config('i18n.default', 'es');
    }

    /** @return list<string> */
    public function supported(): array
    {
        /** @var list<string> $supported */
        $supported = config('i18n.supported', ['es', 'en', 'ca']);

        return $supported;
    }

    public function isSupported(?string $locale): bool
    {
        return $locale !== null && in_array($locale, $this->supported(), true);
    }

    public function resolve(Request $request): string
    {
        $user = $request->user();

        if ($user instanceof User && $this->isSupported($user->locale)) {
            return (string) $user->locale;
        }

        $sessionLocale = $request->session()->get('locale');
        if (is_string($sessionLocale) && $this->isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        return $this->default();
    }
}
