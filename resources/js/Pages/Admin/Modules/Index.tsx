import { Link } from '@inertiajs/react';
import { useMemo } from 'react';
import type { ColumnDefinition } from 'tabulator-tables';
import AppLayout from '@/Layouts/AppLayout';
import ServerTable from '@/Components/Table/ServerTable';

export default function Index() {
    const columns = useMemo<ColumnDefinition[]>(
        () => [
            { title: 'Slug', field: 'slug', width: 140 },
            { title: 'Nombre', field: 'name', widthGrow: 1 },
            { title: 'Etiqueta', field: 'label_text', widthGrow: 1 },
            { title: 'Nivel', field: 'level', width: 120 },
            { title: 'Estado', field: 'status', width: 120 },
        ],
        [],
    );

    return (
        <AppLayout title="Módulos">
            <div className="mb-4 flex items-center justify-between">
                <p className="text-sm text-slate-600">Catálogo global de módulos SaaS.</p>
                <Link
                    href="/admin/modules/create"
                    className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800"
                >
                    Crear
                </Link>
            </div>
            <ServerTable endpoint="/admin/modules/table" columns={columns} />
        </AppLayout>
    );
}
