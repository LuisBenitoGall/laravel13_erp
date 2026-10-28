import {
    Briefcase,
    Home,
    LayoutGrid,
    Settings,
    type LucideIcon,
} from 'lucide-react';

const ICON_MAP: Record<string, LucideIcon> = {
    home: Home,
    settings: Settings,
    briefcase: Briefcase,
};

export function resolveMenuIcon(name: string | null | undefined): LucideIcon {
    if (!name) {
        return LayoutGrid;
    }

    return ICON_MAP[name.trim().toLowerCase()] ?? LayoutGrid;
}
