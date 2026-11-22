import { Bell } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import UserQuickViewPanel, {
    type UserQuickViewPayload,
} from '@/Components/Users/UserQuickViewPanel';
import { nameQuickViewFormatter } from '@/Components/Users/nameQuickViewFormatter';
import AppLayout from '@/Layouts/AppLayout';
import { getJson } from '@/lib/http';

type DirectoryUserRow = {
    id: number;
    name: string;
    surname: string | null;
    email: string;
    status: boolean;
};

export default function Index() {
    const { t } = useTranslation();
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

        void getJson<UserQuickViewPayload>(`/admin/all-users/${quickUserId}/quick`, {
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
            {
                title: t('estado'),
                field: 'status',
                width: 110,
                formatter: (cell) => (cell.getValue() ? t('activo') : t('inactivo')),
            },
        ],
        [t],
    );

    // useCallback: identidad estable al abrir quick-view (evita recreate Tabulator).
    const rowActions = useCallback((): Array<RowAction<DirectoryUserRow>> => [
        {
            id: 'notify',
            icon: Bell,
            labelKey: 'enviar_notificacion',
            onClick: () => {
                toast.info(t('notificacion_mascara'));
            },
        },
    ], [t]);

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'todos_los_usuarios',
                suboptionKey: 'dashboard',
                optionHref: '/admin/all-users',
            }}
            showBack={false}
        >
            <ServerTable<DirectoryUserRow>
                endpoint="/admin/all-users/table"
                columns={columns}
                persistKey="admin.all-users"
                exportable={false}
                rowActions={rowActions}
            />

            <UserQuickViewPanel
                open={quickUserId !== null}
                onClose={() => {
                    setQuickUserId(null);
                }}
                loading={quickLoading}
                payload={quickPayload}
                editHref={null}
            />
        </AppLayout>
    );
}
