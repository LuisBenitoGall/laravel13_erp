import { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/Form/Input';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { cn } from '@/lib/utils';

export type GalleryPickerAsset = {
    id: number;
    name: string;
    alt: string;
    url: string;
};

type PickerMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSelect: (asset: GalleryPickerAsset) => void;
};

type FetchResult = {
    assets: GalleryPickerAsset[];
    meta: PickerMeta;
};

async function fetchPickerPage(
    q: string,
    page: number,
    signal: AbortSignal,
): Promise<FetchResult> {
    const params = new URLSearchParams({
        page: String(page),
        per_page: '24',
    });
    if (q.trim() !== '') {
        params.set('q', q.trim());
    }

    const response = await fetch(`/admin/gallery/picker?${params.toString()}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        signal,
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    const payload = (await response.json()) as {
        assets?: GalleryPickerAsset[];
        meta?: Partial<PickerMeta>;
    };

    return {
        assets: Array.isArray(payload.assets) ? payload.assets : [],
        meta: {
            current_page: Number(payload.meta?.current_page ?? page),
            last_page: Number(payload.meta?.last_page ?? 1),
            per_page: Number(payload.meta?.per_page ?? 24),
            total: Number(payload.meta?.total ?? 0),
        },
    };
}

export function GalleryInsertDialog({ open, onOpenChange, onSelect }: Props) {
    const { t } = useTranslation();
    const [query, setQuery] = useState('');
    const [debouncedQuery, setDebouncedQuery] = useState('');
    const [assets, setAssets] = useState<GalleryPickerAsset[]>([]);
    const [meta, setMeta] = useState<PickerMeta | null>(null);
    const [loading, setLoading] = useState(false);
    const [loadingMore, setLoadingMore] = useState(false);
    const [error, setError] = useState(false);
    const [selectedId, setSelectedId] = useState<number | null>(null);

    const abortRef = useRef<AbortController | null>(null);
    const sentinelRef = useRef<HTMLLIElement | null>(null);
    const scrollRootRef = useRef<HTMLUListElement | null>(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        const timer = window.setTimeout(() => {
            setDebouncedQuery(query);
        }, 300);

        return () => {
            window.clearTimeout(timer);
        };
    }, [query, open]);

    const loadPage = useCallback(async (q: string, page: number, mode: 'replace' | 'append') => {
        abortRef.current?.abort();
        const controller = new AbortController();
        abortRef.current = controller;

        if (mode === 'replace') {
            setLoading(true);
            setError(false);
            setSelectedId(null);
        } else {
            setLoadingMore(true);
        }

        try {
            const result = await fetchPickerPage(q, page, controller.signal);
            setAssets((prev) => (mode === 'replace' ? result.assets : [...prev, ...result.assets]));
            setMeta(result.meta);
            setError(false);
        } catch (err) {
            if (err instanceof DOMException && err.name === 'AbortError') {
                return;
            }
            if (mode === 'replace') {
                setAssets([]);
                setMeta(null);
                setError(true);
            }
        } finally {
            if (!controller.signal.aborted) {
                setLoading(false);
                setLoadingMore(false);
            }
        }
    }, []);

    useEffect(() => {
        if (!open) {
            abortRef.current?.abort();
            setQuery('');
            setDebouncedQuery('');
            setAssets([]);
            setMeta(null);
            setError(false);
            setSelectedId(null);
            setLoading(false);
            setLoadingMore(false);

            return;
        }

        void loadPage(debouncedQuery, 1, 'replace');

        return () => {
            abortRef.current?.abort();
        };
    }, [open, debouncedQuery, loadPage]);

    useEffect(() => {
        if (!open || loading || loadingMore || meta === null) {
            return;
        }

        if (meta.current_page >= meta.last_page) {
            return;
        }

        const root = scrollRootRef.current;
        const sentinel = sentinelRef.current;
        if (root === null || sentinel === null) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const entry = entries[0];
                if (!entry?.isIntersecting || loadingMore) {
                    return;
                }
                if (meta.current_page >= meta.last_page) {
                    return;
                }
                void loadPage(debouncedQuery, meta.current_page + 1, 'append');
            },
            { root, rootMargin: '40px' },
        );

        observer.observe(sentinel);

        return () => {
            observer.disconnect();
        };
    }, [open, loading, loadingMore, meta, debouncedQuery, loadPage, assets.length]);

    function confirm() {
        const asset = assets.find((item) => item.id === selectedId);
        if (!asset) {
            return;
        }

        onSelect(asset);
        onOpenChange(false);
    }

    const showEmpty = !loading && !error && assets.length === 0;
    const emptyKey =
        debouncedQuery.trim() !== '' ? 'galeria_insertar_busqueda_vacia' : 'galeria_insertar_vacia';

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-2xl" data-testid="gallery-insert-dialog">
                <DialogHeader>
                    <DialogTitle>{t('galeria_insertar_titulo')}</DialogTitle>
                </DialogHeader>

                <div className="space-y-3">
                    <Input
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder={t('galeria_insertar_buscar')}
                        aria-label={t('galeria_insertar_buscar')}
                        data-testid="gallery-insert-search"
                        autoComplete="off"
                    />

                    {loading ? (
                        <p className="text-sm text-slate-500">{t('cargando')}</p>
                    ) : null}

                    {!loading && error ? (
                        <p className="text-sm text-red-600">{t('galeria_insertar_error')}</p>
                    ) : null}

                    {showEmpty ? (
                        <p className="text-sm text-slate-500">{t(emptyKey)}</p>
                    ) : null}

                    {!loading && !error && assets.length > 0 ? (
                        <ul
                            ref={scrollRootRef}
                            className="grid max-h-80 grid-cols-2 gap-2 overflow-y-auto sm:grid-cols-3 md:grid-cols-4"
                            data-testid="gallery-insert-grid"
                        >
                            {assets.map((asset) => (
                                <li key={asset.id}>
                                    <button
                                        type="button"
                                        className={cn(
                                            'w-full overflow-hidden rounded-md border bg-slate-50 text-left',
                                            selectedId === asset.id
                                                ? 'border-teal-700 ring-2 ring-teal-700/40'
                                                : 'border-slate-200 hover:border-slate-300',
                                        )}
                                        data-testid={`gallery-insert-asset-${asset.id}`}
                                        onClick={() => setSelectedId(asset.id)}
                                    >
                                        <div className="aspect-square bg-slate-100">
                                            <img
                                                src={asset.url}
                                                alt={asset.alt || asset.name}
                                                className="h-full w-full object-cover"
                                                loading="lazy"
                                            />
                                        </div>
                                        <p
                                            className="truncate px-2 py-1 text-xs text-slate-700"
                                            title={asset.name}
                                        >
                                            {asset.name}
                                        </p>
                                    </button>
                                </li>
                            ))}
                            <li
                                ref={sentinelRef}
                                className="col-span-full h-1 list-none"
                                aria-hidden
                                data-testid="gallery-insert-sentinel"
                            />
                        </ul>
                    ) : null}

                    {loadingMore ? (
                        <p className="text-sm text-slate-500">{t('cargando')}</p>
                    ) : null}
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        {t('cancelar')}
                    </Button>
                    <Button
                        type="button"
                        disabled={selectedId === null}
                        data-testid="gallery-insert-confirm"
                        onClick={confirm}
                    >
                        {t('galeria_insertar_confirmar')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
