export type ProductType = 'physical' | 'service';
export type ProductStatus = 'active' | 'inactive';

export interface ProductRow {
    id: number;
    name: string;
    type: ProductType;
    status: ProductStatus;
    created_at?: string | null;
    updated_at?: string | null;
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
