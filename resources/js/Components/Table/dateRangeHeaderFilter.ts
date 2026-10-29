import type { CellComponent, EmptyCallback } from 'tabulator-tables';

import { dateFormatForLocale, formatDate } from '@/lib/format';

export type DateRangeFilterValue = {
    from: string;
    to: string;
};

type DateRangeEditorParams = {
    fromLabel: string;
    toLabel: string;
    applyLabel: string;
    clearLabel: string;
    emptyLabel: string;
    locale?: string;
};

function isIsoDay(value: string): boolean {
    return /^\d{4}-\d{2}-\d{2}$/.test(value);
}

function readInitial(cell: CellComponent): DateRangeFilterValue {
    const raw = cell.getValue() as DateRangeFilterValue | string | null | undefined;

    if (raw && typeof raw === 'object') {
        return {
            from: typeof raw.from === 'string' && isIsoDay(raw.from) ? raw.from : '',
            to: typeof raw.to === 'string' && isIsoDay(raw.to) ? raw.to : '',
        };
    }

    return { from: '', to: '' };
}

function summarizeRange(value: DateRangeFilterValue, locale: string | undefined): string {
    const from = value.from && isIsoDay(value.from) ? value.from : '';
    const to = value.to && isIsoDay(value.to) ? value.to : '';

    if (!from && !to) {
        return '';
    }

    if (from && to) {
        return `${formatDate(from, locale)} – ${formatDate(to, locale)}`;
    }

    if (from) {
        return `≥ ${formatDate(from, locale)}`;
    }

    return `≤ ${formatDate(to, locale)}`;
}

function normalizeRange(from: string, to: string): DateRangeFilterValue | '' {
    const nextFrom = from && isIsoDay(from) ? from : '';
    const nextTo = to && isIsoDay(to) ? to : '';

    if (!nextFrom && !nextTo) {
        return '';
    }

    return { from: nextFrom, to: nextTo };
}

/**
 * Custom headerFilter Tabulator 6.5: un trigger en cabecera + popover Desde/Hasta.
 * Tabulator no tiene rango nativo; contrato wire = date_between { from, to } ISO día.
 */
export function createDateRangeHeaderFilter(params: DateRangeEditorParams) {
    return function dateRangeHeaderFilter(
        cell: CellComponent,
        onRendered: EmptyCallback,
        success: (value: DateRangeFilterValue | '') => boolean,
        cancel: (value?: unknown) => void,
        _editorParams: unknown,
    ): HTMLElement {
        let committed = readInitial(cell);
        const pattern = dateFormatForLocale(params.locale);

        const wrap = document.createElement('div');
        wrap.className = 'tabulator-date-range-filter';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'tabulator-date-range-trigger';
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.title = `${params.fromLabel} / ${params.toLabel} (${pattern})`;

        const paintTrigger = (): void => {
            const summary = summarizeRange(committed, params.locale);
            trigger.textContent = summary || params.emptyLabel;
            trigger.dataset.empty = summary ? '0' : '1';
            trigger.setAttribute('aria-label', summary || `${params.fromLabel} / ${params.toLabel}`);
        };

        paintTrigger();

        let panel: HTMLDivElement | null = null;
        let fromInput: HTMLInputElement | null = null;
        let toInput: HTMLInputElement | null = null;
        let onDocPointer: ((event: MouseEvent) => void) | null = null;
        let onDocKey: ((event: KeyboardEvent) => void) | null = null;

        const teardownPanel = (): void => {
            if (onDocPointer) {
                document.removeEventListener('mousedown', onDocPointer, true);
                onDocPointer = null;
            }

            if (onDocKey) {
                document.removeEventListener('keydown', onDocKey, true);
                onDocKey = null;
            }

            panel?.remove();
            panel = null;
            fromInput = null;
            toInput = null;
            trigger.setAttribute('aria-expanded', 'false');
        };

        const closeWithoutCommit = (): void => {
            teardownPanel();
            cancel();
        };

        const placePanel = (): void => {
            if (!panel) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const width = Math.max(rect.width, 220);
            let left = rect.left;
            const maxLeft = window.innerWidth - width - 8;

            if (left > maxLeft) {
                left = Math.max(8, maxLeft);
            }

            panel.style.position = 'fixed';
            panel.style.top = `${Math.round(rect.bottom + 4)}px`;
            panel.style.left = `${Math.round(left)}px`;
            panel.style.width = `${Math.round(width)}px`;
            panel.style.zIndex = '80';
        };

        const openPanel = (): void => {
            if (panel) {
                return;
            }

            panel = document.createElement('div');
            panel.className = 'tabulator-date-range-panel';
            panel.setAttribute('role', 'dialog');
            panel.setAttribute('aria-label', `${params.fromLabel} / ${params.toLabel}`);

            const fromField = document.createElement('label');
            fromField.className = 'tabulator-date-range-field';
            const fromCaption = document.createElement('span');
            fromCaption.textContent = params.fromLabel;
            fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = committed.from;
            fromInput.setAttribute('aria-label', `${params.fromLabel} (${pattern})`);
            fromField.append(fromCaption, fromInput);

            const toField = document.createElement('label');
            toField.className = 'tabulator-date-range-field';
            const toCaption = document.createElement('span');
            toCaption.textContent = params.toLabel;
            toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = committed.to;
            toInput.setAttribute('aria-label', `${params.toLabel} (${pattern})`);
            toField.append(toCaption, toInput);

            const actions = document.createElement('div');
            actions.className = 'tabulator-date-range-actions';

            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'tabulator-date-range-btn tabulator-date-range-btn-muted';
            clearBtn.textContent = params.clearLabel;

            const applyBtn = document.createElement('button');
            applyBtn.type = 'button';
            applyBtn.className = 'tabulator-date-range-btn tabulator-date-range-btn-primary';
            applyBtn.textContent = params.applyLabel;

            actions.append(clearBtn, applyBtn);
            panel.append(fromField, toField, actions);
            document.body.appendChild(panel);
            placePanel();
            trigger.setAttribute('aria-expanded', 'true');
            fromInput.focus({ preventScroll: true });

            const apply = (): void => {
                const next = normalizeRange(fromInput?.value ?? '', toInput?.value ?? '');
                committed = next === '' ? { from: '', to: '' } : next;
                success(next);
                paintTrigger();
                teardownPanel();
            };

            const clear = (): void => {
                committed = { from: '', to: '' };
                success('');
                paintTrigger();
                teardownPanel();
            };

            applyBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                apply();
            });
            clearBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                clear();
            });

            for (const input of [fromInput, toInput]) {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        apply();
                    }
                });
            }

            onDocPointer = (event: MouseEvent): void => {
                const target = event.target as Node | null;

                if (!target) {
                    return;
                }

                if (panel?.contains(target) || trigger.contains(target)) {
                    return;
                }

                closeWithoutCommit();
            };
            document.addEventListener('mousedown', onDocPointer, true);

            onDocKey = (event: KeyboardEvent): void => {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeWithoutCommit();
                }
            };
            document.addEventListener('keydown', onDocKey, true);

            window.addEventListener('resize', placePanel, { once: true });
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (panel) {
                closeWithoutCommit();

                return;
            }

            openPanel();
        });

        wrap.appendChild(trigger);

        onRendered(() => {
            trigger.focus({ preventScroll: true });
        });

        return wrap;
    };
}

/** True si el valor de header filter date aporta al menos un extremo ISO. */
export function isActiveDateRangeValue(value: unknown): value is DateRangeFilterValue {
    if (!value || typeof value !== 'object') {
        return false;
    }

    const range = value as DateRangeFilterValue;
    const from = typeof range.from === 'string' ? range.from : '';
    const to = typeof range.to === 'string' ? range.to : '';

    return (from !== '' && isIsoDay(from)) || (to !== '' && isIsoDay(to));
}
