export interface ModuleRow {
    id: number;
    name: string;
    slug: string;
    label_text: string;
    level: string;
    status: string;
    color: string;
    icon: string;
}

export interface FunctionalityRow {
    id: number;
    name: string;
    slug: string;
    label_text: string;
    status: string;
    module_id: number;
}
