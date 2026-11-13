export interface ModuleRow {
    id: number;
    name: string;
    slug: string;
    label_text: string;
    level: string;
    status: string;
    color: string;
    icon: string;
    created_at: string | null;
}

export interface FunctionalityRow {
    id: number;
    name: string;
    slug: string;
    label: Record<string, string>;
    label_text: string;
    status: string;
    module_id: number;
}

export interface CountryRow {
    id: number;
    name: string;
    slug: string | null;
    code: string | null;
    alfa2: string | null;
    alfa3: string | null;
    flag: string | null;
    status: string;
    provinces_count?: number;
}

export interface CurrencyRow {
    id: number;
    name: string;
    slug: string | null;
    code: string | null;
    number: string | null;
    symbol: string | null;
    status: string;
}

export interface UnitRow {
    id: number;
    name: string;
    slug: string | null;
    symbol: string | null;
    status: string;
}

export interface VatTypeCountryOption {
    id: number;
    name: string;
    status: string;
}

export interface VatTypeRow {
    id: number;
    country_id: number;
    country?: { id: number; name: string } | null;
    name: string;
    rate: string;
    equivalence_surcharge: string | null;
    status: string;
}

export interface AccountingAccountTypeRow {
    id: number;
    autoreference: number;
    code: string | null;
    name: string | null;
    mode: string | null;
    level: number;
}

export interface AccountingAccountTypeTreeNode {
    id: number;
    code: string | null;
    name: string | null;
    mode: string | null;
    level: number;
    children: AccountingAccountTypeTreeNode[];
}

export interface ProvinceRow {
    id: number;
    name: string;
    slug: string | null;
    country_id: number;
    status: string;
    towns_count?: number;
}

export interface TownRow {
    id: number;
    name: string;
    slug: string | null;
    province_id: number;
    status: string;
}

export type Company = {
    id: number;
    name: string;
    slug: string;
    tradename: string | null;
    nif: string | null;
    status: string;
    logo: string | null;
    logo_url?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
};

export interface BankRow {
    id: number;
    name: string;
    slug: string | null;
    tradename: string | null;
    lei: string | null;
    eu_code: string | null;
    supervisor_code: string | null;
    swift: string | null;
    swift_source: string | null;
    swift_updated_at: string | null;
    status: string;
}
