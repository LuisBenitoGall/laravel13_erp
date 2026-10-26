# Templates frontend

> Código canónico comentado (React 19 + TS + Inertia). Misma entidad de ejemplo que
> [templates-backend.md](templates-backend.md): **Worksite** (Partners).
> Contratos y catálogo: [frontend.md](frontend.md).

## 1. Tipos (espejo del Resource)

```ts
// resources/js/types/partners.d.ts
// Espejo 1:1 de WorksiteResource. Si cambia el Resource, cambia esto en el mismo PR.

export interface WorksiteRow {
  id: number
  code: string
  name: string
  status: 'active' | 'suspended' | 'closed'
  status_label: string
  customer?: { id: number; name: string }
  commercial_name: string | null
  starts_at: string | null
  credit_limit: string | null
  can: { update: boolean; delete: boolean }
}

export interface WorksiteFormOptions {
  customers: Array<{ value: number; label: string }>
  rates: Array<{ value: number; label: string }>
}
```

## 2. Página Index (patrón 1)

```tsx
// resources/js/Pages/Partners/Worksites/Index.tsx
import { Head, router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/Layouts/AppLayout'
import { ServerTable, type ColumnDef } from '@/Components/Table/ServerTable'
import { fmt } from '@/Components/Table/formatters'
import { Button } from '@/Components/ui/button'
import { usePermissions } from '@/hooks/usePermissions'
import type { WorksiteRow } from '@/types/partners'

export default function Index() {
  const { t } = useTranslation()
  const { can } = usePermissions()

  // Claves i18n castellanas snake_case (R-FE-05): genéricos + familias, sin puntos.
  const columns: ColumnDef<WorksiteRow>[] = [
    { field: 'code', title: t('obra_codigo'), width: 120 },
    { field: 'name', title: t('nombre'), widthGrow: 2 },
    { field: 'customer.name', title: t('cliente'), widthGrow: 1 },
    { field: 'status', title: t('estado'), formatter: fmt.statusBadge('worksite') },
    { field: 'starts_at', title: t('fecha_inicio'), formatter: fmt.date },
    { field: 'credit_limit', title: t('obra_limite_credito'), formatter: fmt.money(2), hozAlign: 'right' },
  ]

  return (
    <AppLayout title={t('obras')}>
      <Head title={t('obras')} />

      <div className="flex items-center justify-between">
        <h1 className="page-title">{t('obras')}</h1>
        {can('worksites.create') && (
          <Button onClick={() => router.visit(route('partners.worksites.create'))}>
            {t('crear')}
          </Button>
        )}
      </div>

      <ServerTable<WorksiteRow>
        endpoint={route('partners.worksites.table')}
        columns={columns}
        persistKey="partners.worksites"
        exportable
        // Acciones por fila según permisos del Resource (`row.can`), no del cliente.
        rowActions={(row) => [
          row.can.update && { label: t('editar'), href: route('partners.worksites.edit', row.id) },
          row.can.delete && {
            label: t('eliminar'),
            destructive: true, // abre ConfirmDialog antes de router.delete
            onClick: () => router.delete(route('partners.worksites.destroy', row.id)),
          },
        ]}
        onRowDblClick={(row) => row.can.update && router.visit(route('partners.worksites.edit', row.id))}
      />
    </AppLayout>
  )
}
```

## 3. Formulario compartido Create/Edit (patrón 2)

Kit MVP (`Components/Form`): `Field`, `FormField` (RHF), `Input`, `Textarea`,
`NumberInput`, `Select` (filtro local; `onSearch` opcional), `DatePicker`, `Checkbox`,
`Switch`. Referencia real migrada: `Partners/Companies/components/CompanyForm.tsx`.

```tsx
// Ejemplo mínimo Inertia useForm + kit (espejo FormRequest en servidor)
import { useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import { Field, Input, NumberInput, Select, DatePicker } from '@/Components/Form'

export default function WorksiteForm({ worksite, options }) {
  const { t } = useTranslation()
  const form = useForm({
    code: worksite?.code ?? '',
    customer_company_id: worksite?.customer?.id?.toString() ?? '',
    starts_at: worksite?.starts_at ?? null,
    credit_limit: worksite?.credit_limit ?? '',
  })

  return (
    <form onSubmit={(e) => { e.preventDefault(); form.post('/…') }} className="space-y-4">
      <Field label={t('obra_codigo')} htmlFor="code" error={form.errors.code} required>
        {/* R-FE-08: Form/Input → ui/input (maxLength 255 por defecto) */}
        <Input
          id="code"
          value={form.data.code}
          onChange={(e) => form.setData('code', e.target.value)}
          maxLength={32}
        />
      </Field>

      <Field label={t('cliente')} htmlFor="customer" error={form.errors.customer_company_id}>
        {/* Select: filtro local; onSearch opcional para remoto futuro */}
        <Select
          id="customer"
          options={options.customers.map((c) => ({ value: String(c.value), label: c.label }))}
          value={form.data.customer_company_id}
          onChange={(v) => form.setData('customer_company_id', v)}
        />
      </Field>

      <Field label={t('fecha_inicio')} htmlFor="starts_at" error={form.errors.starts_at}>
        <DatePicker
          id="starts_at"
          value={form.data.starts_at}
          onChange={(v) => form.setData('starts_at', v)}
        />
      </Field>

      <Field label={t('obra_limite_credito')} htmlFor="credit_limit" error={form.errors.credit_limit}>
        <NumberInput
          id="credit_limit"
          decimals={2}
          value={form.data.credit_limit}
          onChange={(v) => form.setData('credit_limit', v)}
        />
      </Field>
    </form>
  )
}
```

Con **react-hook-form** + zod, usar `FormField` + `Controller` (mismo label/error):

```tsx
import { FormProvider, useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { FormField, Input } from '@/Components/Form'

const schema = z.object({ name: z.string().min(1).max(255) })

function Example() {
  const form = useForm({ resolver: zodResolver(schema), defaultValues: { name: '' } })
  return (
    <FormProvider {...form}>
      <FormField name="name" label={t('nombre')} required>
        {({ field }) => (
          <Input
            id="name"
            value={String(field.value ?? '')}
            onChange={(e) => field.onChange(e.target.value)}
            onBlur={field.onBlur}
            ref={field.ref as React.Ref<HTMLInputElement>}
          />
        )}
      </FormField>
    </FormProvider>
  )
}
```

```tsx
// resources/js/Pages/Partners/Worksites/Edit.tsx  (Create.tsx es igual sin `worksite`)
import { useTranslation } from 'react-i18next'
import AppLayout from '@/Layouts/AppLayout'
import { RecordEditLayout } from '@/Components/Records/RecordEditLayout'
import WorksiteForm from './components/WorksiteForm'
import type { WorksiteFormOptions, WorksiteRow } from '@/types/partners'

export default function Edit({ worksite, options }: { worksite: WorksiteRow; options: WorksiteFormOptions }) {
  const { t } = useTranslation()

  return (
    <AppLayout title={worksite.name}>
      <RecordEditLayout
        title={worksite.name}
        subtitle={worksite.code}
        status={{ value: worksite.status, label: worksite.status_label, map: 'worksite' }}
        backHref={route('partners.worksites.index')}
        tabs={[
          { key: 'general', label: t('general'), content: <WorksiteForm worksite={worksite} options={options} /> },
          // Tabs pesadas con lazy load vía partial reload (frontend.md §4 Records/Tabs)
          { key: 'activity', label: t('actividad'), lazy: 'activity' },
        ]}
      />
    </AppLayout>
  )
}
```

## 4. Notas de patrón documento (patrón 3)

Las páginas de documento (presupuesto, pedido, factura, recepción…) parten del template de
Edit y añaden — no reinventan:

1. `ActionBar` con las transiciones del enum de estado (cada botón → `router.post` a la
   ruta de la transición; deshabilitado según `can` del Resource).
2. `LineItemsEditor` en la tab principal, alimentado por el Resource de líneas.
   Los totales se calculan con `lib/line-calc` (espejo del cálculo backend, unit-testeado
   en ambos lados con los mismos casos).
3. Panel de totales fijo (base, descuentos, IVA por tipo, total, margen).
4. Bloqueo por estado: documento no editable → el form entero en modo lectura
   (`fieldset disabled`), nunca ocultando campos.
