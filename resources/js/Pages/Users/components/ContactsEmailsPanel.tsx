import { Copy, Mail, Plus, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input, Textarea } from '@/Components/Form';
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
import ContactsPhonesPanel, { type UserPhoneItem } from '@/Pages/Users/components/ContactsPhonesPanel';

export type UserEmailItem = {
    id: number;
    email: string;
    description: string | null;
};

type Props = {
    emails: UserEmailItem[];
    phones: UserPhoneItem[];
    storeUrl: string;
    destroyUrl: (id: number) => string;
    phonesStoreUrl: string;
    phonesUpdateUrl: (id: number) => string;
    phonesDestroyUrl: (id: number) => string;
    phonesMarkPrimaryUrl: (id: number) => string;
    canManage: boolean;
};

export default function ContactsEmailsPanel({
    emails,
    phones,
    storeUrl,
    destroyUrl,
    phonesStoreUrl,
    phonesUpdateUrl,
    phonesDestroyUrl,
    phonesMarkPrimaryUrl,
    canManage,
}: Props) {
    const { t } = useTranslation();
    const [createOpen, setCreateOpen] = useState(false);
    const [pendingDelete, setPendingDelete] = useState<UserEmailItem | null>(null);

    const form = useForm({
        email: '',
        description: '',
    });

    function submitCreate(e: FormEvent) {
        e.preventDefault();
        form.post(storeUrl, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setCreateOpen(false);
            },
        });
    }

    async function copyEmail(value: string) {
        const ok = await copyText(value);
        if (ok) {
            toast.success(t('email_copiado'));
        } else {
            toast.error(t('email_copiar_error'));
        }
    }

    function confirmDelete() {
        if (pendingDelete === null) {
            return;
        }
        router.delete(destroyUrl(pendingDelete.id), {
            preserveScroll: true,
        });
        setPendingDelete(null);
    }

    return (
        <div className="grid gap-6 lg:grid-cols-2">
            <section className="rounded-xl border border-slate-200 bg-white p-5">
                <div className="mb-4 flex items-center justify-between gap-3">
                    <h2 className="text-sm font-semibold text-slate-900">{t('emails_adicionales')}</h2>
                    {canManage ? (
                        <Button type="button" size="sm" onClick={() => setCreateOpen(true)}>
                            <Plus className="mr-1 h-4 w-4" />
                            {t('anadir_email')}
                        </Button>
                    ) : null}
                </div>

                {emails.length === 0 ? (
                    <p className="text-sm text-slate-500">{t('sin_emails')}</p>
                ) : (
                    <ul className="space-y-3">
                        {emails.map((item) => (
                            <li
                                key={item.id}
                                className="rounded-lg border border-slate-200 bg-slate-50/60 p-3"
                                data-testid={`user-email-card-${item.id}`}
                            >
                                <p className="text-sm font-medium text-slate-900">{item.email}</p>
                                {item.description ? (
                                    <p className="mt-1 text-sm text-slate-600">{item.description}</p>
                                ) : null}
                                <div className="mt-3 flex flex-wrap items-center gap-2">
                                    <Button type="button" variant="secondary" size="sm" asChild>
                                        <a href={`mailto:${item.email}`}>
                                            <Mail className="mr-1 h-3.5 w-3.5" />
                                            {t('mailto')}
                                        </a>
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        onClick={() => void copyEmail(item.email)}
                                    >
                                        <Copy className="mr-1 h-3.5 w-3.5" />
                                        {t('copiar')}
                                    </Button>
                                    {canManage ? (
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
                                    ) : null}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            <ContactsPhonesPanel
                phones={phones}
                storeUrl={phonesStoreUrl}
                updateUrl={phonesUpdateUrl}
                destroyUrl={phonesDestroyUrl}
                markPrimaryUrl={phonesMarkPrimaryUrl}
                canManage={canManage}
            />

            <Dialog
                open={createOpen}
                onOpenChange={(open) => {
                    setCreateOpen(open);
                    if (!open) {
                        form.clearErrors();
                        form.reset();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('anadir_email')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <Field label={t('email')} htmlFor="user-email-new" required error={form.errors.email}>
                            <Input
                                id="user-email-new"
                                type="email"
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                                maxLength={255}
                                autoComplete="off"
                            />
                        </Field>
                        <Field
                            label={t('observaciones')}
                            htmlFor="user-email-description"
                            error={form.errors.description}
                        >
                            <Textarea
                                id="user-email-description"
                                value={form.data.description}
                                onChange={(e) => form.setData('description', e.target.value)}
                                maxLength={255}
                                rows={3}
                            />
                        </Field>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setCreateOpen(false)}
                            >
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
                title={t('eliminar_email_confirmacion')}
                description={pendingDelete?.email}
                confirmLabel={t('eliminar')}
                onConfirm={confirmDelete}
            />
        </div>
    );
}
