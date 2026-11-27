import { Link, router } from '@inertiajs/react';
import { Trash2, X } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import { resolveMenuIcon } from '@/lib/menuIcons';

type PreferenceCta = {
    id: number;
    name: string;
    url: string;
    color: string | null;
    icon: string | null;
};

type NotePostIt = {
    id: number;
    title: string;
    note: string;
    created_at: string | null;
};

type Props = {
    preferences?: PreferenceCta[];
    notes?: NotePostIt[];
};

function formatNoteDate(value: string | null, locale: string): string {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(date);
}

export default function Dashboard({ preferences = [], notes = [] }: Props) {
    const { t, i18n } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'dashboard',
                suboptionKey: 'dashboard',
                optionHref: '/dashboard',
            }}
            showBack={false}
            actions={[]}
        >
            {notes.length > 0 ? (
                <section className="mt-6 space-y-3">
                    <h2 className="text-sm font-semibold text-slate-900">{t('mis_notas')}</h2>
                    <ul className="dashboard-notes">
                        {notes.map((item) => (
                            <li key={item.id}>
                                <div className="dashboard-note">
                                    <small className="absolute top-1 right-1 text-[10px] leading-none text-black/80">
                                        {formatNoteDate(item.created_at, i18n.language)}
                                    </small>
                                    <p className="mt-4 line-clamp-2 text-sm font-semibold leading-snug text-black">
                                        {item.title}
                                    </p>
                                    <p className="mt-1 line-clamp-4 text-xs leading-snug text-black/90 whitespace-pre-wrap">
                                        {item.note}
                                    </p>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon-sm"
                                        className="absolute right-1 bottom-1 h-7 w-7 text-black/70 hover:bg-black/5 hover:text-red-600"
                                        aria-label={t('eliminar_nota')}
                                        onClick={() => {
                                            router.delete(`/account/notes/${item.id}`, {
                                                preserveScroll: true,
                                            });
                                        }}
                                    >
                                        <Trash2 aria-hidden className="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}

            <section className="mt-8 space-y-3">
                <h2 className="text-sm font-semibold text-slate-900">
                    {t('mis_opciones_preferidas')}
                </h2>
                {preferences.length === 0 ? (
                    <p className="text-sm text-slate-500">{t('sin_favoritos')}</p>
                ) : (
                    <ul className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {preferences.map((preference) => {
                            const Icon = resolveMenuIcon(preference.icon);
                            const color = preference.color || '#64748b';

                            return (
                                <li key={preference.id} className="relative">
                                    <Link
                                        href={preference.url}
                                        className="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 pr-10 shadow-sm transition hover:border-slate-300"
                                    >
                                        <span
                                            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white"
                                            style={{ backgroundColor: color }}
                                            aria-hidden
                                        >
                                            <Icon className="h-5 w-5" />
                                        </span>
                                        <span className="truncate text-sm font-medium text-slate-900">
                                            {preference.name}
                                        </span>
                                    </Link>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon-sm"
                                        className="absolute top-1 right-1 text-slate-500 hover:text-red-600"
                                        aria-label={t('eliminar_favorito')}
                                        onClick={() => {
                                            router.delete(`/account/preferences/${preference.id}`, {
                                                preserveScroll: true,
                                            });
                                        }}
                                    >
                                        <X aria-hidden className="h-4 w-4" />
                                    </Button>
                                </li>
                            );
                        })}
                    </ul>
                )}
            </section>
        </AppLayout>
    );
}
