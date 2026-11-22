import { createElement } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { Eye } from 'lucide-react';
import type { CellComponent } from 'tabulator-tables';

type DirectoryNameRow = {
    id: number;
    name: string;
};

/**
 * Celda nombre con icono «ver detalle» a la izquierda (Tabulator HTMLElement).
 */
export function nameQuickViewFormatter(
    cell: CellComponent,
    options: {
        detailLabel: string;
        onQuickView: (userId: number) => void;
    },
): HTMLElement {
    const row = cell.getRow().getData() as DirectoryNameRow;
    const wrap = document.createElement('div');
    wrap.className = 'flex min-w-0 items-center gap-1.5';

    const button = document.createElement('button');
    button.type = 'button';
    button.className =
        'inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-brand';
    button.title = options.detailLabel;
    button.setAttribute('aria-label', options.detailLabel);
    button.innerHTML = renderToStaticMarkup(
        createElement(Eye, { size: 14, strokeWidth: 2, 'aria-hidden': true }),
    );
    button.addEventListener('click', (event) => {
        event.stopPropagation();
        options.onQuickView(row.id);
    });

    const label = document.createElement('span');
    label.className = 'min-w-0 truncate';
    label.textContent = row.name ?? '';

    wrap.append(button, label);

    return wrap;
}
