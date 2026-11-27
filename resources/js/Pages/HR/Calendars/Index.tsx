import { router, useForm } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Copy, Layers } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import YearCalendarView, { type YearCalendarDay } from '@/Components/Calendar/YearCalendarView';
import { Field, FormActions, Input, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import AppLayout from '@/Layouts/AppLayout';
import type {
    DayTypeRow,
    IdNameOption,
    WorkCalendarDayRow,
    WorkCalendarPayload,
} from '@/types/calendar';

type Props = {
    year: number;
    workplaces: IdNameOption[];
    day_types: DayTypeRow[];
    selected_workplace_id: number | null;
    calendar: WorkCalendarPayload | null;
    days: WorkCalendarDayRow[];
    can: {
        update: boolean;
        manage_day_types: boolean;
    };
};

type DayModalState = {
    date: string;
    existing: WorkCalendarDayRow | null;
};

export default function Index({
    year,
    workplaces,
    day_types,
    selected_workplace_id,
    calendar,
    days,
    can,
}: Props) {
    const { t } = useTranslation();
    const [dayModal, setDayModal] = useState<DayModalState | null>(null);
    const [inheritOpen, setInheritOpen] = useState(false);
    const [copyOpen, setCopyOpen] = useState(false);

    const workplaceOptions = useMemo(
        () => workplaces.map((w) => ({ value: String(w.id), label: w.name })),
        [workplaces],
    );

    const dayTypeOptions = useMemo(
        () => day_types.map((d) => ({ value: String(d.id), label: d.name })),
        [day_types],
    );

    const copyTargetOptions = useMemo(
        () =>
            workplaces
                .filter((w) => w.id !== selected_workplace_id)
                .map((w) => ({ value: String(w.id), label: w.name })),
        [workplaces, selected_workplace_id],
    );

    const yearDays: YearCalendarDay[] = useMemo(
        () =>
            days.map((day) => ({
                date: day.date,
                name: day.name,
                color: day.day_type?.color ?? null,
            })),
        [days],
    );

    const dayForm = useForm({
        date: '',
        name: '',
        day_type_id: day_types[0] ? String(day_types[0].id) : '',
    });

    const inheritForm = useForm({
        from_year: String(year),
        to_year: String(year + 1),
    });

    const copyForm = useForm({
        year: String(year),
        target_workplace_id: copyTargetOptions[0]?.value ?? '',
    });

    const visitCalendar = (workplaceId: number | null, nextYear: number) => {
        router.get(
            '/rrhh/calendars',
            {
                workplace_id: workplaceId ?? undefined,
                year: nextYear,
            },
            { preserveState: false },
        );
    };

    const openDay = (date: string) => {
        if (!can.update || !calendar) {
            return;
        }

        const existing = days.find((d) => d.date === date) ?? null;
        dayForm.setData({
            date,
            name: existing?.name ?? '',
            day_type_id: existing
                ? String(existing.day_type_id)
                : day_types[0]
                  ? String(day_types[0].id)
                  : '',
        });
        dayForm.clearErrors();
        setDayModal({ date, existing });
    };

    const submitDay = (event: FormEvent) => {
        event.preventDefault();
        if (!calendar) {
            return;
        }

        dayForm.transform((data) => ({
            date: data.date,
            name: data.name,
            day_type_id: Number(data.day_type_id),
        }));
        dayForm.post(`/rrhh/calendars/${calendar.id}/days`, {
            preserveScroll: true,
            onSuccess: () => setDayModal(null),
        });
    };

    const deleteDay = () => {
        if (!calendar || !dayModal?.existing) {
            return;
        }

        router.delete(`/rrhh/calendars/${calendar.id}/days/${dayModal.existing.id}`, {
            preserveScroll: true,
            onSuccess: () => setDayModal(null),
        });
    };

    const submitInherit = (event: FormEvent) => {
        event.preventDefault();
        if (!calendar) {
            return;
        }
        if (!window.confirm(t('heredar_anio_confirm'))) {
            return;
        }

        inheritForm.transform((data) => ({
            from_year: Number(data.from_year),
            to_year: Number(data.to_year),
        }));
        inheritForm.post(`/rrhh/calendars/${calendar.id}/inherit`, {
            onSuccess: () => setInheritOpen(false),
        });
    };

    const submitCopy = (event: FormEvent) => {
        event.preventDefault();
        if (!calendar) {
            return;
        }
        if (!window.confirm(t('copiar_calendario_confirm'))) {
            return;
        }

        copyForm.transform((data) => ({
            year: Number(data.year),
            target_workplace_id: Number(data.target_workplace_id),
        }));
        copyForm.post(`/rrhh/calendars/${calendar.id}/copy`, {
            onSuccess: () => setCopyOpen(false),
        });
    };

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'calendarios',
                suboptionKey: 'dashboard',
                optionHref: '/rrhh/calendars',
            }}
            showBack={false}
            actions={[
                ...(can.manage_day_types
                    ? [
                          {
                              type: 'button' as const,
                              labelKey: 'tipos_de_jornada',
                              icon: Layers,
                              onClick: () => router.visit('/rrhh/day-types'),
                          },
                      ]
                    : []),
            ]}
        >
            <div className="space-y-4">
                <div className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 md:flex-row md:items-end md:justify-between">
                    <div className="grid flex-1 grid-cols-1 gap-3 md:grid-cols-3">
                        <Field label={t('seleccionar_sede')} htmlFor="calendar-workplace">
                            <Select
                                id="calendar-workplace"
                                options={workplaceOptions}
                                value={
                                    selected_workplace_id != null
                                        ? String(selected_workplace_id)
                                        : ''
                                }
                                placeholder={t('elegir')}
                                onChange={(value) =>
                                    visitCalendar(value ? Number(value) : null, year)
                                }
                            />
                        </Field>
                        <Field label={t('anio')} htmlFor="calendar-year">
                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="icon"
                                    onClick={() =>
                                        visitCalendar(selected_workplace_id, year - 1)
                                    }
                                >
                                    <ChevronLeft className="h-4 w-4" />
                                </Button>
                                <Input
                                    id="calendar-year"
                                    value={String(year)}
                                    readOnly
                                    className="text-center"
                                />
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="icon"
                                    onClick={() =>
                                        visitCalendar(selected_workplace_id, year + 1)
                                    }
                                >
                                    <ChevronRight className="h-4 w-4" />
                                </Button>
                            </div>
                        </Field>
                    </div>

                    {calendar && can.update ? (
                        <div className="flex flex-wrap gap-2">
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => {
                                    inheritForm.setData({
                                        from_year: String(year),
                                        to_year: String(year + 1),
                                    });
                                    setInheritOpen(true);
                                }}
                            >
                                {t('heredar_anio')}
                            </Button>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => {
                                    copyForm.setData({
                                        year: String(year),
                                        target_workplace_id: copyTargetOptions[0]?.value ?? '',
                                    });
                                    setCopyOpen(true);
                                }}
                                disabled={copyTargetOptions.length === 0}
                            >
                                <Copy className="mr-2 h-4 w-4" />
                                {t('copiar_a_sede')}
                            </Button>
                        </div>
                    ) : null}
                </div>

                {!selected_workplace_id ? (
                    <p className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600">
                        {t('sin_sede_seleccionada')}
                    </p>
                ) : (
                    <YearCalendarView
                        year={year}
                        days={yearDays}
                        readOnly={!can.update}
                        onYearChange={(next) => visitCalendar(selected_workplace_id, next)}
                        onDayClick={(date) => openDay(date)}
                    />
                )}
            </div>

            <Dialog open={dayModal !== null} onOpenChange={(open) => !open && setDayModal(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('dia_calendario')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitDay} className="space-y-4">
                        <Field label={t('fecha')} htmlFor="day-date">
                            <Input id="day-date" value={dayForm.data.date} readOnly />
                        </Field>
                        <Field
                            label={t('nombre')}
                            htmlFor="day-name"
                            error={dayForm.errors.name}
                            required
                        >
                            <Input
                                id="day-name"
                                value={dayForm.data.name}
                                maxLength={255}
                                onChange={(e) => dayForm.setData('name', e.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('tipos_de_jornada')}
                            htmlFor="day-type"
                            error={dayForm.errors.day_type_id}
                            required
                        >
                            <Select
                                id="day-type"
                                options={dayTypeOptions}
                                value={dayForm.data.day_type_id}
                                onChange={(value) => dayForm.setData('day_type_id', value)}
                                placeholder={t('elegir')}
                            />
                        </Field>
                        <DialogFooter className="gap-2 sm:justify-between">
                            {dayModal?.existing ? (
                                <Button
                                    type="button"
                                    variant="secondary"
                                    className="text-red-700 hover:bg-red-50"
                                    onClick={deleteDay}
                                >
                                    {t('eliminar')}
                                </Button>
                            ) : (
                                <span />
                            )}
                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    variant="secondary"
                                    onClick={() => setDayModal(null)}
                                >
                                    {t('cancelar')}
                                </Button>
                                <Button type="submit" disabled={dayForm.processing}>
                                    {t('guardar')}
                                </Button>
                            </div>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={inheritOpen} onOpenChange={setInheritOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('heredar_anio')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitInherit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Field
                                label={t('anio_origen')}
                                htmlFor="inherit-from"
                                error={inheritForm.errors.from_year}
                                required
                            >
                                <Input
                                    id="inherit-from"
                                    inputMode="numeric"
                                    value={inheritForm.data.from_year}
                                    onChange={(e) =>
                                        inheritForm.setData(
                                            'from_year',
                                            e.target.value.replace(/\D/g, ''),
                                        )
                                    }
                                />
                            </Field>
                            <Field
                                label={t('anio_destino')}
                                htmlFor="inherit-to"
                                error={inheritForm.errors.to_year}
                                required
                            >
                                <Input
                                    id="inherit-to"
                                    inputMode="numeric"
                                    value={inheritForm.data.to_year}
                                    onChange={(e) =>
                                        inheritForm.setData(
                                            'to_year',
                                            e.target.value.replace(/\D/g, ''),
                                        )
                                    }
                                />
                            </Field>
                        </div>
                        <FormActions>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setInheritOpen(false)}
                            >
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={inheritForm.processing}>
                                {t('guardar')}
                            </Button>
                        </FormActions>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={copyOpen} onOpenChange={setCopyOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('copiar_a_sede')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCopy} className="space-y-4">
                        <Field
                            label={t('anio')}
                            htmlFor="copy-year"
                            error={copyForm.errors.year}
                            required
                        >
                            <Input
                                id="copy-year"
                                inputMode="numeric"
                                value={copyForm.data.year}
                                onChange={(e) =>
                                    copyForm.setData('year', e.target.value.replace(/\D/g, ''))
                                }
                            />
                        </Field>
                        <Field
                            label={t('sede_destino')}
                            htmlFor="copy-target"
                            error={copyForm.errors.target_workplace_id}
                            required
                        >
                            <Select
                                id="copy-target"
                                options={copyTargetOptions}
                                value={copyForm.data.target_workplace_id}
                                onChange={(value) =>
                                    copyForm.setData('target_workplace_id', value)
                                }
                                placeholder={t('elegir')}
                            />
                        </Field>
                        <FormActions>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setCopyOpen(false)}
                            >
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={copyForm.processing}>
                                {t('guardar')}
                            </Button>
                        </FormActions>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
