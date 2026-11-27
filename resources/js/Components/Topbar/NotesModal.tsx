import { Link, useForm, usePage } from '@inertiajs/react';
import { StickyNote } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, Input, Textarea } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

type SharedNotesProps = {
    notes_count?: number;
};

export default function NotesModal() {
    const { t } = useTranslation();
    const { notes_count = 0 } = usePage<SharedNotesProps>().props;
    const [open, setOpen] = useState(false);

    const form = useForm({
        title: '',
        note: '',
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/account/notes', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setOpen(false);
            },
        });
    }

    return (
        <>
            <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                className="relative"
                aria-label={t('notas')}
                onClick={() => setOpen(true)}
            >
                <StickyNote aria-hidden />
                <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-100 px-1 text-[10px] font-medium text-amber-900">
                    {notes_count}
                </span>
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('nueva_nota')}</DialogTitle>
                    </DialogHeader>
                    <div className="mb-2">
                        <Link
                            href="/dashboard"
                            className="text-sm text-slate-600 underline-offset-2 hover:underline"
                            onClick={() => setOpen(false)}
                        >
                            {t('ver_todas_las_notas')}
                        </Link>
                    </div>
                    <form onSubmit={submit} className="space-y-3">
                        <Field
                            label={t('titulo')}
                            htmlFor="note-title"
                            error={form.errors.title}
                            required
                        >
                            <Input
                                id="note-title"
                                value={form.data.title}
                                maxLength={150}
                                autoComplete="off"
                                onChange={(e) => form.setData('title', e.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('nota')}
                            htmlFor="note-body"
                            error={form.errors.note}
                            required
                        >
                            <Textarea
                                id="note-body"
                                value={form.data.note}
                                rows={5}
                                maxLength={1000}
                                onChange={(e) => form.setData('note', e.target.value)}
                            />
                        </Field>
                        <DialogFooter>
                            <Button type="submit" size="sm" disabled={form.processing}>
                                {t('guardar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
