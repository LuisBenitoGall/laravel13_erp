import { Copy, MessageCircle, Pencil, Phone, Plus, Star, Trash2 } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input, Select, Switch, Textarea } from '@/Components/Form';
import ConfirmDialog from '@/Components/Feedback/ConfirmDialog';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { copyText } from '@/lib/clipboard';
import { formatPhoneDisplay } from '@/lib/format';
import { cn } from '@/lib/utils';

export type UserPhoneItem = {
    id: number;
    e164: string;
    ext: string | null;
    type: string;
    label: string | null;
    notes: string | null;
    is_primary: boolean;
    is_whatsapp: boolean;
    is_verified: boolean;
};

type PhoneFormData = {
    e164: string;
    ext: string;
    type: string;
    label: string;
    notes: string;
    is_whatsapp: boolean;
    is_primary: boolean;
};

type Props = {
    phones: UserPhoneItem[];
    storeUrl: string;
    updateUrl: (id: number) => string;
    destroyUrl: (id: number) => string;
    markPrimaryUrl: (id: number) => string;
    canManage: boolean;
};

const emptyForm = (): PhoneFormData => ({
    e164: '',
    ext: '',
    type: 'mobile',
    label: '',
    notes: '',
    is_whatsapp: false,
    is_primary: false,
});

function waMeUrl(e164: string): string {
    return `https://wa.me/${e164.replace(/\D/g, '')}`;
}

function displayNumber(phone: UserPhoneItem): string {
    const formatted = formatPhoneDisplay(phone.e164);

    return phone.ext ? `${formatted} ext. ${phone.ext}` : formatted;
}

export default function ContactsPhonesPanel({
    phones,
    storeUrl,
    updateUrl,
    destroyUrl,
    markPrimaryUrl,
    canManage,
}: Props) {
    const { t } = useTranslation();
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState<UserPhoneItem | null>(null);
    const [pendingDelete, setPendingDelete] = useState<UserPhoneItem | null>(null);

    const form = useForm<PhoneFormData>(emptyForm());

    const typeOptions = useMemo(
        () => [
            { value: 'mobile', label: t('telefono_tipo_mobile') },
            { value: 'landline', label: t('telefono_tipo_landline') },
            { value: 'fax', label: t('telefono_tipo_fax') },
            { value: 'other', label: t('telefono_tipo_other') },
        ],
        [t],
    );

    function openCreate() {
        setEditing(null);
        form.clearErrors();
        form.setData(emptyForm());
        setModalOpen(true);
    }

    function openEdit(phone: UserPhoneItem) {
        setEditing(phone);
        form.clearErrors();
        form.setData({
            e164: phone.e164,
            ext: phone.ext ?? '',
            type: phone.type,
            label: phone.label ?? '',
            notes: phone.notes ?? '',
            is_whatsapp: phone.is_whatsapp,
            is_primary: phone.is_primary,
        });
        setModalOpen(true);
    }

    function closeModal() {
        setModalOpen(false);
        setEditing(null);
        form.clearErrors();
        form.setData(emptyForm());
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        };

        if (editing !== null) {
            form.put(updateUrl(editing.id), options);
        } else {
            form.post(storeUrl, options);
        }
    }

    async function copyPhone(value: string) {
        const ok = await copyText(value);
        if (ok) {
            toast.success(t('telefono_copiado'));
        } else {
            toast.error(t('telefono_copiar_error'));
        }
    }

    function markPrimary(phone: UserPhoneItem) {
        if (phone.is_primary) {
            return;
        }
        router.post(markPrimaryUrl(phone.id), {}, { preserveScroll: true });
    }

    function confirmDelete() {
        if (pendingDelete === null) {
            return;
        }
        router.delete(destroyUrl(pendingDelete.id), { preserveScroll: true });
        setPendingDelete(null);
    }

    return (
        <section className="rounded-xl border border-slate-200 bg-white p-5">
            <div className="mb-4 flex items-center justify-between gap-3">
                <h2 className="text-sm font-semibold text-slate-900">{t('telefonos')}</h2>
                {canManage ? (
                    <Button type="button" size="sm" onClick={openCreate}>
                        <Plus className="mr-1 h-4 w-4" />
                        {t('anadir_telefono')}
                    </Button>
                ) : null}
            </div>

            {phones.length === 0 ? (
                <p className="text-sm text-slate-500">{t('sin_telefonos')}</p>
            ) : (
                <ul className="space-y-3">
                    {phones.map((item) => (
                        <li
                            key={item.id}
                            className={cn(
                                'rounded-lg border bg-slate-50/60 p-3',
                                item.is_primary ? 'border-teal-600' : 'border-slate-200',
                            )}
                            data-testid={`user-phone-card-${item.id}`}
                        >
                            <div className="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p className="text-base font-bold text-slate-900">
                                        {displayNumber(item)}
                                    </p>
                                    {item.label ? (
                                        <p className="mt-0.5 text-sm text-slate-600">{item.label}</p>
                                    ) : null}
                                    <p className="mt-1 text-xs text-slate-500">
                                        {t(`telefono_tipo_${item.type}`)}
                                    </p>
                                </div>
                                <div className="flex flex-wrap gap-1.5">
                                    {item.is_primary ? (
                                        <span className="rounded-full bg-teal-100 px-2 py-0.5 text-xs font-medium text-teal-800">
                                            {t('badge_primario')}
                                        </span>
                                    ) : null}
                                    {item.is_whatsapp ? (
                                        <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                            {t('badge_whatsapp')}
                                        </span>
                                    ) : null}
                                    {!item.is_verified ? (
                                        <span className="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">
                                            {t('badge_no_verificado')}
                                        </span>
                                    ) : null}
                                </div>
                            </div>

                            <div className="mt-3 flex flex-wrap items-center gap-2">
                                <Button type="button" variant="secondary" size="sm" asChild>
                                    <a href={`tel:${item.e164}`}>
                                        <Phone className="mr-1 h-3.5 w-3.5" />
                                        {t('llamar')}
                                    </a>
                                </Button>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    onClick={() => void copyPhone(item.e164)}
                                >
                                    <Copy className="mr-1 h-3.5 w-3.5" />
                                    {t('copiar')}
                                </Button>
                                {item.is_whatsapp ? (
                                    <Button type="button" variant="secondary" size="sm" asChild>
                                        <a
                                            href={waMeUrl(item.e164)}
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            <MessageCircle className="mr-1 h-3.5 w-3.5" />
                                            {t('whatsapp')}
                                        </a>
                                    </Button>
                                ) : null}
                                {canManage ? (
                                    <>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            disabled={item.is_primary}
                                            onClick={() => markPrimary(item)}
                                            title={t('marcar_primario')}
                                        >
                                            <Star
                                                className={cn(
                                                    'mr-1 h-3.5 w-3.5',
                                                    item.is_primary && 'fill-current text-amber-500',
                                                )}
                                            />
                                            {t('marcar_primario')}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            onClick={() => openEdit(item)}
                                        >
                                            <Pencil className="mr-1 h-3.5 w-3.5" />
                                            {t('editar')}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            className="text-red-700 hover:text-red-800"
                                            onClick={() => setPendingDelete(item)}
                                        >
                                            <Trash2 className="mr-1 h-3.5 w-3.5" />
                                            {t('eliminar')}
                                        </Button>
                                    </>
                                ) : null}
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            <Dialog
                open={modalOpen}
                onOpenChange={(open) => {
                    if (!open) {
                        closeModal();
                    } else {
                        setModalOpen(true);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {editing !== null ? t('editar_telefono') : t('nuevo_telefono')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submit} className="space-y-4">
                        <Field
                            label={t('numero_telefono')}
                            htmlFor="user-phone-e164"
                            required
                            error={form.errors.e164}
                        >
                            <Input
                                id="user-phone-e164"
                                value={form.data.e164}
                                onChange={(e) => form.setData('e164', e.target.value)}
                                maxLength={30}
                                autoComplete="tel"
                                placeholder="+34…"
                            />
                        </Field>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label={t('extension')}
                                htmlFor="user-phone-ext"
                                error={form.errors.ext}
                            >
                                <Input
                                    id="user-phone-ext"
                                    value={form.data.ext}
                                    onChange={(e) => form.setData('ext', e.target.value)}
                                    maxLength={10}
                                />
                            </Field>
                            <Field
                                label={t('tipo_telefono')}
                                htmlFor="user-phone-type"
                                required
                                error={form.errors.type}
                            >
                                <Select
                                    id="user-phone-type"
                                    options={typeOptions}
                                    value={form.data.type}
                                    onChange={(value) => form.setData('type', value)}
                                />
                            </Field>
                        </div>
                        <Field
                            label={t('etiqueta')}
                            htmlFor="user-phone-label"
                            error={form.errors.label}
                        >
                            <Input
                                id="user-phone-label"
                                value={form.data.label}
                                onChange={(e) => form.setData('label', e.target.value)}
                                maxLength={50}
                            />
                        </Field>
                        <Field
                            label={t('observaciones')}
                            htmlFor="user-phone-notes"
                            error={form.errors.notes}
                        >
                            <Textarea
                                id="user-phone-notes"
                                value={form.data.notes}
                                onChange={(e) => form.setData('notes', e.target.value)}
                                rows={3}
                            />
                        </Field>
                        <div className="flex flex-col gap-3 sm:flex-row sm:gap-x-8">
                            <Switch
                                id="user-phone-whatsapp"
                                checked={form.data.is_whatsapp}
                                onCheckedChange={(checked) => form.setData('is_whatsapp', checked)}
                                label={t('badge_whatsapp')}
                            />
                            <Switch
                                id="user-phone-primary"
                                checked={form.data.is_primary}
                                onCheckedChange={(checked) => form.setData('is_primary', checked)}
                                label={t('badge_primario')}
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="secondary" onClick={closeModal}>
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={form.processing}>
                                {t('guardar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDelete(null);
                    }
                }}
                title={t('eliminar_telefono_confirmacion')}
                description={pendingDelete ? displayNumber(pendingDelete) : undefined}
                confirmLabel={t('eliminar')}
                onConfirm={confirmDelete}
            />
        </section>
    );
}
