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
            { title: 'Nombre comercial', field: 'tradename', widthGrow: 1 },
            { title: 'NIF', field: 'nif', width: 120 },
            { title: 'Estado', field: 'status', width: 120 },
        ],
        [],
    );

    return (
        <AppLayout title="Empresas">
            <div className="mb-4 flex items-center justify-between">
                <p className="text-sm text-slate-600">Catálogo de empresas (tenant).</p>
                <Link
                    href="/partners/companies/create"
                    className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800"
                >
                    Crear
                </Link>
            </div>
            <ServerTable endpoint="/partners/companies/table" columns={columns} />
        </AppLayout>
    );
}
