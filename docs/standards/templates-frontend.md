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

  // Columnas tipadas; títulos SIEMPRE i18n (R-FE-05); formatters del catálogo (R-FE-04).
  const columns: ColumnDef<WorksiteRow>[] = [
    { field: 'code', title: t('partners.worksites.code'), width: 120 },
    { field: 'name', title: t('partners.worksites.name'), widthGrow: 2 },
    { field: 'customer.name', title: t('partners.worksites.customer'), widthGrow: 1 },
    { field: 'status', title: t('common.status'), formatter: fmt.statusBadge('worksite') },
    { field: 'starts_at', title: t('common.start_date'), formatter: fmt.date },
    { field: 'credit_limit', title: t('partners.worksites.credit_limit'), formatter: fmt.money(2), hozAlign: 'right' },
  ]

  return (
    <AppLayout title={t('partners.worksites.title')}>
      <Head title={t('partners.worksites.title')} />

      <div className="flex items-center justify-between">
        <h1 className="page-title">{t('partners.worksites.title')}</h1>
        {can('worksites.create') && (
          <Button onClick={() => router.visit(route('partners.worksites.create'))}>
            {t('common.create')}
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
          row.can.update && { label: t('common.edit'), href: route('partners.worksites.edit', row.id) },
          row.can.delete && {
            label: t('common.delete'),
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

```tsx
// resources/js/Pages/Partners/Worksites/components/WorksiteForm.tsx
import { useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import { Field, NumberInput, SearchSelect, DatePicker, FormActions } from '@/Components/Form'
import type { WorksiteFormOptions, WorksiteRow } from '@/types/partners'

interface Props {
  worksite?: WorksiteRow                 // undefined → Create
  options: WorksiteFormOptions
}

// El esquema de validación cliente es ESPEJO del FormRequest (playbook §5):
// misma regla, mismo mensaje i18n. El servidor sigue siendo la única verdad.
export default function WorksiteForm({ worksite, options }: Props) {
  const { t } = useTranslation()

  const form = useForm({
    code: worksite?.code ?? '',
    name: worksite?.name ?? '',
    customer_company_id: worksite?.customer?.id ?? null,
    rate_id: null as number | null,
    starts_at: worksite?.starts_at ?? null,
    ends_at: null as string | null,
    credit_limit: worksite?.credit_limit ?? null,
  })

  const submit = () =>
    worksite
      ? form.put(route('partners.worksites.update', worksite.id), { preserveScroll: true })
      : form.post(route('partners.worksites.store'))

  return (
    <form onSubmit={(e) => { e.preventDefault(); submit() }} className="form-grid">
      <Field label={t('partners.worksites.code')} error={form.errors.code} required>
        <input value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} maxLength={32} />
      </Field>

      <Field label={t('partners.worksites.customer')} error={form.errors.customer_company_id} required>
        {/* SearchSelect: combobox con búsqueda remota — pieza del kit, no un select ad-hoc */}
        <SearchSelect
          options={options.customers}
          value={form.data.customer_company_id}
          onChange={(v) => form.setData('customer_company_id', v)}
        />
      </Field>

      <Field label={t('common.start_date')} error={form.errors.starts_at}>
        <DatePicker value={form.data.starts_at} onChange={(v) => form.setData('starts_at', v)} />
      </Field>

      <Field label={t('partners.worksites.credit_limit')} error={form.errors.credit_limit}>
        {/* NumberInput con contexto de decimales — nunca un <input type=number> pelado (R-FE-04) */}
        <NumberInput decimals={2} value={form.data.credit_limit}
          onChange={(v) => form.setData('credit_limit', v)} />
      </Field>

      <FormActions processing={form.processing} isDirty={form.isDirty} />
    </form>
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
          { key: 'general', label: t('common.general'), content: <WorksiteForm worksite={worksite} options={options} /> },
          // Tabs pesadas con lazy load vía partial reload (frontend.md §4 Records/Tabs)
          { key: 'activity', label: t('common.activity'), lazy: 'activity' },
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
