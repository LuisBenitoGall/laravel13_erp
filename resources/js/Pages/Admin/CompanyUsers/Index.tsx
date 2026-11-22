import { Pencil, Plus } from 'lucide-react';
import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, {
    type ColumnDef,
    type MassAction,
    type RowAction,
} from '@/Components/Table/ServerTable';
import UserQuickViewPanel, {
    type UserQuickViewPayload,
} from '@/Components/Users/UserQuickViewPanel';
import { nameQuickViewFormatter } from '@/Components/Users/nameQuickViewFormatter';
import AppLayout from '@/Layouts/AppLayout';
import { getJson, patchJson } from '@/lib/http';

type DirectoryUserRow = {
    id: number;
    name: string;
    surname: string | null;
    email: string;
    status: boolean;
};

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [quickUserId, setQuickUserId] = useState<number | null>(null);
    const [quickPayload, setQuickPayload] = useState<UserQuickViewPayload | null>(null);
    const [quickLoading, setQuickLoading] = useState(false);

    useEffect(() => {
        if (quickUserId === null) {
            setQuickPayload(null);
            setQuickLoading(false);

            return;
        }

        const controller = new AbortController();
        setQuickLoading(true);

        void getJson<UserQuickViewPayload>(`/admin/company-users/${quickUserId}/quick`, {
            signal: controller.signal,
        })
            .then((data) => {
                setQuickPayload(data);
            })
            .catch((error: unknown) => {
                if (controller.signal.aborted) {
                    return;
                }
                setQuickPayload(null);
                toast.error(t('error_carga'));
                console.error(error);
            })
            .finally(() => {
                if (!controller.signal.aborted) {
                    setQuickLoading(false);
                }
            });

        return () => {
            controller.abort();
        };
    }, [quickUserId, t]);

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 1,
                headerFilterKind: 'string',
                formatter: (cell) =>
                    nameQuickViewFormatter(cell, {
                        detailLabel: t('ver_detalle'),
                        onQuickView: setQuickUserId,
                    }),
            },
            {
                title: t('apellidos'),
                field: 'surname',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('email'),
                field: 'email',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
        ],
        [t],
    );

    const refreshTable = useCallback((): void => {
        setTableKey((key) => key + 1);
    }, []);

    const toastFirstError = useCallback((errors: Record<string, string>): void => {
        const message = Object.values(errors)[0];
        if (message) {
            toast.error(String(message));
        }
    }, []);

    // useCallback: identidad estable. Si rowActions se recrea al abrir el panel,
    // ServerTable destruye/recrea Tabulator y recarga /table (no deseado).
    const rowActions = useCallback(
        (row: DirectoryUserRow): Array<RowAction<DirectoryUserRow>> => [
            {
                id: 'toggle-status',
                kind: 'toggle',
                labelKey: row.status ? 'desactivar' : 'activar',
                checked: row.status,
                confirm: {
                    titleKey: 'confirmar_desactivar_usuario',
                    descriptionKey: 'confirmar_desactivar_usuario_detalle',
                },
                onToggle: async (_row, checked) => {
                    const result = await patchJson<{ status: boolean }>(
                        `/admin/company-users/${row.id}/status`,
                        { status: checked },
                    );

                    return { status: result.status };
                },
            },
            {
                id: 'edit',
                icon: Pencil,
                labelKey: 'editar',
                onClick: () => {
                    router.visit(`/admin/company-users/${row.id}/edit`);
                },
            },
            {
                id: 'delete',
                labelKey: 'eliminar',
                variant: 'danger',
                confirm: true,
                onClick: (current) => {
                    router.delete(`/admin/company-users/${current.id}`, {
                        preserveScroll: true,
                        onSuccess: refreshTable,
                        onError: toastFirstError,
                    });
                },
            },
        ],
        [refreshTable, toastFirstError],
    );

    const massActions = useMemo<MassAction<DirectoryUserRow>[]>(
        () => [
            {
                id: 'activate',
                labelKey: 'activar',
                onAction: (rows) => {
                    router.post(
                        '/admin/company-users/bulk-status',
                        {
                            ids: rows.map((row) => row.id),
                            status: true,
                        },
                        {
                            preserveScroll: true,
                            onSuccess: refreshTable,
                            onError: toastFirstError,
                        },
                    );
                },
            },
            {
                id: 'deactivate',
                labelKey: 'desactivar',
                confirm: {
                    titleKey: 'confirmar_desactivar_usuarios_masivo',
                    descriptionKey: 'confirmar_desactivar_usuario_detalle',
                },
                onAction: (rows) => {
                    router.post(
                        '/admin/company-users/bulk-status',
                        {
                            ids: rows.map((row) => row.id),
                            status: false,
                        },
                        {
                            preserveScroll: true,
                            onSuccess: refreshTable,
                            onError: toastFirstError,
                        },
                    );
                },
            },
            {
                id: 'delete',
                labelKey: 'eliminar',
                variant: 'danger',
                confirm: { titleKey: 'confirmar_eliminar_masivo' },
                onAction: (rows) => {
                    router.post(
                        '/admin/company-users/bulk-detach',
                        {
                            ids: rows.map((row) => row.id),
                        },
                        {
                            preserveScroll: true,
                            onSuccess: refreshTable,
                            onError: toastFirstError,
                        },
                    );
                },
            },
        ],
        [refreshTable, toastFirstError],
    );

    const editHref =
        quickPayload?.can_edit && quickUserId !== null
            ? `/admin/company-users/${quickUserId}/edit`
            : null;

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'usuarios_empresa',
                suboptionKey: 'dashboard',
                optionHref: '/admin/company-users',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'crear_usuario',
                    icon: Plus,
                    onClick: () => {
                        router.visit('/admin/company-users/create');
                    },
                },
            ]}
        >
            <ServerTable<DirectoryUserRow>
                key={tableKey}
                endpoint="/admin/company-users/table"
                columns={columns}
                persistKey="admin.company-users"
                exportable={false}
                rowActions={rowActions}
                massActions={massActions}
            />

            <UserQuickViewPanel
                open={quickUserId !== null}
                onClose={() => {
                    setQuickUserId(null);
                }}
                loading={quickLoading}
                payload={quickPayload}
                editHref={editHref}
            />
        </AppLayout>
    );
}
