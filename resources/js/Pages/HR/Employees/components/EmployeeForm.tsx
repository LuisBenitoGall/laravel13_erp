import { router, useForm } from '@inertiajs/react';
import { FormEvent, useEffect, useMemo, useRef } from 'react';
import { useTranslation } from 'react-i18next';

import {
    DatePicker,
    Field,
    FormActions,
    Input,
    NumberInput,
    Select,
    Switch,
    Textarea,
} from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import type { EmployeeOptions, EmployeeRow } from '@/types/h_r';

type EmployeeFormData = {
    user_id: string;
    contract_type_id: string;
    workplace_id: string;
    job_title: string;
    start_date: string | null;
    end_date: string | null;
    weekly_hours: string;
    year_salary: string;
    time_control: boolean;
    observations: string;
};

type Props = {
    /** create: alta; edit: edita el periodo; renew: cierra el activo y crea el nuevo. */
    mode: 'create' | 'edit' | 'renew';
    submitUrl: string;
    method?: 'post' | 'put';
    options: EmployeeOptions;
    defaultUserId?: number | null;
    initial?: Partial<EmployeeRow>;
};

function addDaysIso(start: string, days: number): string {
    const date = new Date(`${start}T00:00:00`);
    date.setDate(date.getDate() + days);
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
}

export default function EmployeeForm({
    mode,
    submitUrl,
    method = 'post',
    options,
    defaultUserId = null,
    initial,
}: Props) {
    const { t } = useTranslation();

    const form = useForm<EmployeeFormData>({
        user_id: initial?.user_id
            ? String(initial.user_id)
            : defaultUserId
              ? String(defaultUserId)
              : '',
        contract_type_id: initial?.contract_type_id ? String(initial.contract_type_id) : '',
        workplace_id: initial?.workplace_id ? String(initial.workplace_id) : '',
        job_title: initial?.job_title ?? '',
        start_date: mode === 'renew' ? null : (initial?.start_date ?? null),
        end_date: mode === 'renew' ? null : (initial?.end_date ?? null),
        weekly_hours: initial?.weekly_hours ?? '40',
        year_salary: initial?.year_salary ?? '',
        time_control: initial?.time_control ?? true,
        observations: initial?.observations ?? '',
    });

    const selectedType = useMemo(
        () => options.contract_types.find((c) => String(c.id) === form.data.contract_type_id),
        [options.contract_types, form.data.contract_type_id],
    );
    const endDateRequired = selectedType != null && selectedType.duration != null;
    const lastSuggested = useRef<string | null>(null);

    useEffect(() => {
        if (
            selectedType?.duration == null ||
            !form.data.start_date ||
            selectedType.duration < 1
        ) {
            return;
        }

        const suggested = addDaysIso(form.data.start_date, selectedType.duration);
        const current = form.data.end_date;
        const canReplace =
            current === null || current === '' || current === lastSuggested.current;

        if (canReplace && current !== suggested) {
            lastSuggested.current = suggested;
            form.setData('end_date', suggested);
        }
        // Solo reaccionar a tipo / inicio; no a end_date manual.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedType?.duration, form.data.contract_type_id, form.data.start_date]);

    const userOptions = useMemo(
        () => options.users.map((u) => ({ value: String(u.id), label: u.name })),
        [options.users],
    );
    const contractTypeOptions = useMemo(
        () => options.contract_types.map((c) => ({ value: String(c.id), label: c.name })),
        [options.contract_types],
    );
    const workplaceOptions = useMemo(
        () => [
            { value: '', label: '—' },
            ...options.workplaces.map((w) => ({ value: String(w.id), label: w.name })),
        ],
        [options.workplaces],
    );

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        const payload = {
            ...(mode === 'create' ? { user_id: Number(form.data.user_id) } : {}),
            contract_type_id: Number(form.data.contract_type_id),
            workplace_id: form.data.workplace_id ? Number(form.data.workplace_id) : null,
            job_title: form.data.job_title || null,
            start_date: form.data.start_date,
            end_date: form.data.end_date,
            weekly_hours: form.data.weekly_hours,
            year_salary: form.data.year_salary || null,
            time_control: form.data.time_control,
            observations: form.data.observations || null,
        };
        form.transform(() => payload);
        if (method === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-mode={mode}
        >
            {mode === 'create' ? (
                <Field
                    label={t('usuario')}
                    htmlFor="employee-user"
                    error={form.errors.user_id}
                    required
                >
                    <Select
                        id="employee-user"
                        options={userOptions}
                        value={form.data.user_id}
                        onChange={(value) => form.setData('user_id', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
            ) : null}

            <div className="grid gap-4 md:grid-cols-2">
                <Field
                    label={t('tipo_de_contrato')}
                    htmlFor="employee-contract-type"
                    error={form.errors.contract_type_id}
                    required
                >
                    <Select
                        id="employee-contract-type"
                        options={contractTypeOptions}
                        value={form.data.contract_type_id}
                        onChange={(value) => form.setData('contract_type_id', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
                <Field
                    label={t('centro_de_trabajo')}
                    htmlFor="employee-workplace"
                    error={form.errors.workplace_id}
                >
                    <Select
                        id="employee-workplace"
                        options={workplaceOptions}
                        value={form.data.workplace_id}
                        onChange={(value) => form.setData('workplace_id', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
            </div>

            <Field label={t('puesto')} htmlFor="employee-job-title" error={form.errors.job_title}>
                <Input
                    id="employee-job-title"
                    value={form.data.job_title}
                    maxLength={255}
                    onChange={(e) => form.setData('job_title', e.target.value)}
                />
            </Field>

            <div className="grid gap-4 md:grid-cols-4">
                <Field
                    label={t('fecha_inicio')}
                    htmlFor="employee-start-date"
                    error={form.errors.start_date}
                    required
                >
                    <DatePicker
                        id="employee-start-date"
                        value={form.data.start_date}
                        onChange={(value) => form.setData('start_date', value)}
                    />
                </Field>
                <Field
                    label={t('fecha_fin')}
                    htmlFor="employee-end-date"
                    error={form.errors.end_date}
                    required={endDateRequired}
                >
                    <DatePicker
                        id="employee-end-date"
                        value={form.data.end_date}
                        onChange={(value) => form.setData('end_date', value)}
                    />
                </Field>
                <Field
                    label={t('horas_semanales')}
                    htmlFor="employee-weekly-hours"
                    error={form.errors.weekly_hours}
                    required
                >
                    <NumberInput
                        id="employee-weekly-hours"
                        decimals={2}
                        maxLength={6}
                        value={form.data.weekly_hours}
                        onChange={(value) => form.setData('weekly_hours', value)}
                    />
                </Field>
                <Field
                    label={t('salario_anual')}
                    htmlFor="employee-year-salary"
                    error={form.errors.year_salary}
                >
                    <NumberInput
                        id="employee-year-salary"
                        decimals={2}
                        maxLength={13}
                        value={form.data.year_salary}
                        onChange={(value) => form.setData('year_salary', value)}
                    />
                </Field>
            </div>

            <div className="flex min-h-10 items-center">
                <Switch
                    id="employee-time-control"
                    label={t('control_horario')}
                    checked={form.data.time_control}
                    onCheckedChange={(checked) => form.setData('time_control', checked)}
                />
            </div>

            <Field
                label={t('observaciones')}
                htmlFor="employee-observations"
                error={form.errors.observations}
            >
                <Textarea
                    id="employee-observations"
                    rows={4}
                    value={form.data.observations}
                    onChange={(e) => form.setData('observations', e.target.value)}
                />
            </Field>

            {mode === 'renew' && (form.errors as Record<string, string | undefined>).employee ? (
                <p className="text-sm text-red-600" role="alert">
                    {(form.errors as Record<string, string | undefined>).employee}
                </p>
            ) : null}

            <FormActions>
                <Button
                    type="button"
                    variant="secondary"
                    onClick={() => router.visit('/hr/employees')}
                >
                    {t('cancelar')}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {mode === 'renew' ? t('renovar') : t('guardar')}
                </Button>
            </FormActions>
        </form>
    );
}
