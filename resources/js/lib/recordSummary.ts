import type { RecordSummaryField } from '@/Components/Records/RecordSummary';

export type AuditRecordSummaryInput = {
    created_at?: string | null;
    created_by?: string | null;
    updated_at?: string | null;
    updated_by?: string | null;
};

/**
 * Campos de auditoría habituales para concatenar en `RecordSummary.fields`.
 * Omite claves con valor null/undefined/vacío.
 */
export function auditRecordSummaryFields(
    input: AuditRecordSummaryInput,
): RecordSummaryField[] {
    const fields: RecordSummaryField[] = [];

    if (hasValue(input.created_at)) {
        fields.push({ labelKey: 'creado', value: input.created_at, tone: 'muted' });
    }

    if (hasValue(input.created_by)) {
        fields.push({ labelKey: 'autor', value: input.created_by });
    }

    if (hasValue(input.updated_at)) {
        fields.push({ labelKey: 'actualizado', value: input.updated_at, tone: 'muted' });
    }

    if (hasValue(input.updated_by)) {
        fields.push({ labelKey: 'actualizado_por', value: input.updated_by });
    }

    return fields;
}

function hasValue(value: string | null | undefined): value is string {
    return typeof value === 'string' && value.length > 0;
}
