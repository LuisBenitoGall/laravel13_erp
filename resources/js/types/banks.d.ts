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
