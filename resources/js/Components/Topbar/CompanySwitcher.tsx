import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { ChevronsUpDown } from 'lucide-react';

type CompanyOption = { id: number; name: string };

export default function CompanySwitcher({
    tenantId,
    tenantName,
    companies,
}: {
    tenantId: number | null;
    tenantName: string | null;
    companies: CompanyOption[];
}) {
    const { t } = useTranslation();

    if (companies.length <= 1) {
        return (
            <span className="max-w-[12rem] truncate text-sm font-medium text-slate-700">
                {tenantName ?? companies[0]?.name ?? ''}
            </span>
        );
    }

    const current =
        companies.find((company) => company.id === tenantId)?.name ?? tenantName ?? t('empresa');

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="secondary" size="sm" className="max-w-[14rem]">
                    <span className="truncate">{current}</span>
                    <ChevronsUpDown className="size-3.5 opacity-60" aria-hidden />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {companies.map((company) => (
                    <DropdownMenuItem
                        key={company.id}
                        onSelect={() => {
                            if (company.id === tenantId) {
                                return;
                            }
                            router.post('/companies/switch', { company_id: company.id });
                        }}
                    >
                        {company.name}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
