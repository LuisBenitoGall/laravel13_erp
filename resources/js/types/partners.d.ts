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
