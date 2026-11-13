import { FormEvent, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Field } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

type Props = {
    companyId: number;
    logoUrl: string | null;
};

export default function CompanyLogoPanel({ companyId, logoUrl }: Props) {
    const { t } = useTranslation();
    const [preview, setPreview] = useState<string | null>(null);
    const form = useForm<{ logo: File | null }>({
        logo: null,
    });

    function onFileChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.target.files?.[0] ?? null;
        form.setData('logo', file);
        form.clearErrors('logo');
        if (preview) {
            URL.revokeObjectURL(preview);
        }
        setPreview(file ? URL.createObjectURL(file) : null);
    }

    function onSubmit(event: FormEvent) {
        event.preventDefault();
        form.post(`/admin/companies/${companyId}/logo`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('logo');
                if (preview) {
                    URL.revokeObjectURL(preview);
                }
                setPreview(null);
            },
        });
    }

    const shown = preview ?? logoUrl;

    return (
        <div className="rounded-xl border border-slate-200 bg-white p-5">
            <form onSubmit={onSubmit} className="space-y-4">
                <p className="text-sm text-slate-600">{t('logo_unico_ayuda')}</p>
                <div className="flex h-32 w-56 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                    {shown ? (
                        <img src={shown} alt="" className="h-full w-full object-contain" />
                    ) : (
                        <span className="text-sm text-slate-400">{t('sin_logo')}</span>
                    )}
                </div>
                <Field label={t('logo')} htmlFor="company-logo" error={form.errors.logo}>
                    <input
                        id="company-logo"
                        type="file"
                        accept="image/*"
                        className="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-teal-700 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-teal-800"
                        onChange={onFileChange}
                    />
                </Field>
                <p className="text-xs text-slate-500">{t('logo_maximo_500_kb')}</p>
                <Button type="submit" disabled={form.processing || form.data.logo === null}>
                    {t('subir_logo')}
                </Button>
            </form>
        </div>
    );
}
