import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';
import type { AccountingAccountTypeTreeNode } from '@/types/admin';

type PageProps = {
    tree: AccountingAccountTypeTreeNode[];
};

function TreeNodes({ nodes }: { nodes: AccountingAccountTypeTreeNode[] }) {
    if (nodes.length === 0) {
        return null;
    }

    return (
        <ul className="space-y-1 border-l border-teal-700/20 pl-3">
            {nodes.map((node) => (
                <li key={node.id} className="py-0.5">
                    <div className="flex gap-2 text-sm">
                        <span className="font-mono text-muted-foreground">{node.code}</span>
                        <span>{node.name}</span>
                    </div>
                    {node.children.length > 0 ? <TreeNodes nodes={node.children} /> : null}
                </li>
            ))}
        </ul>
    );
}

export default function Show() {
    const { t } = useTranslation();
    const { tree } = usePage<PageProps>().props;

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'grupos_contables',
                suboptionKey: 'ver_plan_pgc',
                optionHref: '/admin/accounting-account-types',
            }}
            backHref="/admin/accounting-account-types"
            showBack
        >
            <div className="space-y-4">
                <h1 className="text-lg font-semibold">{t('plan_grupos_contables')}</h1>
                <p className="text-sm text-muted-foreground">{t('plan_grupos_contables_ayuda')}</p>
                <div className="rounded-xl border border-slate-200 bg-white p-4">
                    {tree.length === 0 ? (
                        <p className="text-sm text-muted-foreground">{t('sin_datos')}</p>
                    ) : (
                        <TreeNodes nodes={tree} />
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
