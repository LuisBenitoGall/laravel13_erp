import { useEffect, useState, type ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import type { TableFilter } from '@/Components/Table/tableFilters';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { deleteJson, getJson, patchJson, postJson } from '@/lib/http';

export type { TableFilter };

export type AdvancedSearchApi = {
    filters: TableFilter[];
    setFilters: (filters: TableFilter[]) => void;
};

type AdvancedSearchSlot = ReactNode | ((api: AdvancedSearchApi) => ReactNode);

type PresetRow = {
    id: number;
    name: string;
    table_key: string;
    filters: TableFilter[];
};

/**
 * Modal genérico de búsqueda avanzada (opt-in).
 * Disparador canónico: toolbar ServerTable (`onOpenAdvancedSearch`).
 * Con `tableKey`: lista/CRUD de presets (seleccionar solo rellena draft).
 */
export default function AdvancedSearchModal({
    open,
    onOpenChange,
    titleKey = 'busqueda_avanzada',
    fields,
    children,
    seedFilters,
    onApply,
    onClear,
    tableKey,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    titleKey?: string;
    fields?: AdvancedSearchSlot;
    children?: AdvancedSearchSlot;
    /** Filtros aplicados actuales; se copian al draft al abrir. */
    seedFilters?: TableFilter[];
    onApply: (filters: TableFilter[]) => void;
    onClear?: () => void;
    /** Si se define, habilita zona presets (máx. 5 por table_key). */
    tableKey?: string;
}) {
    const { t } = useTranslation();
    const [draft, setDraft] = useState<TableFilter[]>([]);
    const [presets, setPresets] = useState<PresetRow[]>([]);
    const [presetName, setPresetName] = useState('');
    const [renamingId, setRenamingId] = useState<number | null>(null);
    const [renameValue, setRenameValue] = useState('');
    const [busy, setBusy] = useState(false);

    const slot = fields !== undefined ? fields : children;
    const api: AdvancedSearchApi = {
        filters: draft,
        setFilters: setDraft,
    };

    const loadPresets = async () => {
        if (!tableKey) {
            return;
        }

        try {
            const payload = await getJson<{ data: PresetRow[] }>(
                `/account/table-filter-presets?table_key=${encodeURIComponent(tableKey)}`,
            );
            setPresets(payload.data ?? []);
        } catch {
            setPresets([]);
        }
    };

    useEffect(() => {
        if (!open) {
            return;
        }

        setDraft(seedFilters ? [...seedFilters] : []);
        setPresetName('');
        setRenamingId(null);
        setRenameValue('');
        void loadPresets();
        // Solo al abrir.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    const handleSavePreset = async () => {
        if (!tableKey || !presetName.trim()) {
            return;
        }

        setBusy(true);
        try {
            await postJson<{ data: PresetRow }>('/account/table-filter-presets', {
                table_key: tableKey,
                name: presetName.trim(),
                filters: draft,
            });
            setPresetName('');
            await loadPresets();
            toast.success(t('preset_filtro_guardado'));
        } catch (error) {
            toast.error(error instanceof Error ? error.message : t('error'));
        } finally {
            setBusy(false);
        }
    };

    const handleRename = async (preset: PresetRow) => {
        const next = renameValue.trim();
        if (!next) {
            return;
        }

        setBusy(true);
        try {
            await patchJson<{ data: PresetRow }>(
                `/account/table-filter-presets/${preset.id}`,
                { name: next },
            );
            setRenamingId(null);
            setRenameValue('');
            await loadPresets();
        } catch (error) {
            toast.error(error instanceof Error ? error.message : t('error'));
        } finally {
            setBusy(false);
        }
    };

    const handleDelete = async (preset: PresetRow) => {
        setBusy(true);
        try {
            await deleteJson(`/account/table-filter-presets/${preset.id}`);
            await loadPresets();
        } catch (error) {
            toast.error(error instanceof Error ? error.message : t('error'));
        } finally {
            setBusy(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{t(titleKey)}</DialogTitle>
                </DialogHeader>
                <div className="grid gap-3 py-2">
                    {typeof slot === 'function' ? slot(api) : slot}
                </div>

                {tableKey ? (
                    <div className="space-y-3 border-t border-slate-200 pt-3">
                        <p className="text-sm font-medium text-slate-800">
                            {t('presets_filtro')}
                        </p>
                        {presets.length === 0 ? (
                            <p className="text-sm text-slate-500">{t('sin_presets_filtro')}</p>
                        ) : (
                            <ul className="max-h-40 space-y-1 overflow-auto">
                                {presets.map((preset) => (
                                    <li
                                        key={preset.id}
                                        className="flex flex-wrap items-center gap-2 rounded-md border border-slate-100 px-2 py-1.5"
                                    >
                                        {renamingId === preset.id ? (
                                            <>
                                                <Input
                                                    className="h-8 min-w-0 flex-1"
                                                    value={renameValue}
                                                    maxLength={255}
                                                    onChange={(e) =>
                                                        setRenameValue(e.target.value)
                                                    }
                                                />
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    disabled={busy}
                                                    onClick={() => void handleRename(preset)}
                                                >
                                                    {t('guardar')}
                                                </Button>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="secondary"
                                                    onClick={() => setRenamingId(null)}
                                                >
                                                    {t('cancelar')}
                                                </Button>
                                            </>
                                        ) : (
                                            <>
                                                <button
                                                    type="button"
                                                    className="min-w-0 flex-1 truncate text-left text-sm text-slate-800 hover:underline"
                                                    onClick={() =>
                                                        setDraft(
                                                            Array.isArray(preset.filters)
                                                                ? [...preset.filters]
                                                                : [],
                                                        )
                                                    }
                                                >
                                                    {preset.name}
                                                </button>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={busy}
                                                    onClick={() => {
                                                        setRenamingId(preset.id);
                                                        setRenameValue(preset.name);
                                                    }}
                                                >
                                                    {t('renombrar')}
                                                </Button>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={busy}
                                                    onClick={() => void handleDelete(preset)}
                                                >
                                                    {t('eliminar')}
                                                </Button>
                                            </>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                        <div className="flex flex-wrap items-end gap-2">
                            <div className="min-w-[12rem] flex-1">
                                <label
                                    className="mb-1 block text-xs text-slate-600"
                                    htmlFor="preset-filter-name"
                                >
                                    {t('nombre_preset_filtro')}
                                </label>
                                <Input
                                    id="preset-filter-name"
                                    value={presetName}
                                    maxLength={255}
                                    onChange={(e) => setPresetName(e.target.value)}
                                    placeholder={t('nombre_preset_filtro')}
                                />
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                disabled={busy || !presetName.trim()}
                                onClick={() => void handleSavePreset()}
                            >
                                {t('guardar_preset_filtro')}
                            </Button>
                        </div>
                    </div>
                ) : null}

                <DialogFooter className="gap-2 sm:justify-end">
                    {onClear ? (
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => {
                                setDraft([]);
                                onClear();
                            }}
                        >
                            {t('limpiar')}
                        </Button>
                    ) : null}
                    <Button type="button" variant="secondary" onClick={() => onOpenChange(false)}>
                        {t('cancelar')}
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            onApply(draft);
                            onOpenChange(false);
                        }}
                    >
                        {t('aplicar')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
