export type ProductType = 'physical' | 'service';
export type ProductStatus = 'active' | 'inactive';
export type FamilyStatus = 'active' | 'inactive';

export interface ProductRow {
    id: number;
    name: string;
    type: ProductType;
    status: ProductStatus;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface FamilyRow {
    id: number;
    name: string;
    slug: string;
    status: FamilyStatus;
    created_at?: string | null;
    updated_at?: string | null;
}

export type CategoryStatus = 'active' | 'inactive';

export interface CategoryRow {
    id: number;
    name: string;
    slug: string;
    status: CategoryStatus;
    parent_id: number | null;
    parent_name?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface CategoryParentOption {
    value: string;
    label: string;
}

export interface ProductImageItem {
    id: number;
    url: string;
    featured: boolean;
    public: boolean;
}

export interface ProductDocItem {
    id: number;
    original_name: string;
    mime: string;
    size_bytes: number;
    public: boolean;
    download_url: string;
    created_at: string | null;
}
