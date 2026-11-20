export type GalleryAsset = {
    id: number;
    company_id: number | null;
    name: string;
    alt: string;
    url: string;
    mime: string | null;
    width: number | null;
    height: number | null;
    bytes: number | null;
};
