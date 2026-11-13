import { Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { useMemo, useState } from 'react';

import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { resolveMenuIcon } from '@/lib/menuIcons';
import { cn } from '@/lib/utils';

export type AsideNavItem = {
    slug: string;
    label: string;
    color: string;
    icon: string;
    /** Module hoja (p. ej. dashboard): enlace directo sin hijos. */
    href?: string;
    functionalities: Array<{ slug: string; label: string; href: string }>;
};

const primaryIdle =
    'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-100';

const primaryActive =
    'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium bg-slate-100 text-slate-900';

const subIdle =
    'block rounded-md py-1.5 pl-7 pr-2 text-xs font-normal text-slate-500 hover:bg-slate-50 hover:text-slate-700';

const subActive =
    'block rounded-md py-1.5 pl-7 pr-2 text-xs font-medium bg-slate-100 text-slate-800';

const railButton =
    'flex h-10 w-10 items-center justify-center rounded-md text-slate-700 hover:bg-slate-100';

const railButtonActive = 'flex h-10 w-10 items-center justify-center rounded-md bg-slate-100 text-slate-900';

function normalizePath(value: string): string {
    const path = value.split('?')[0] ?? value;

    if (path.length > 1 && path.endsWith('/')) {
        return path.slice(0, -1);
    }

    return path || '/';
}

/** Ficha self `/admin/profile*`: ningún ítem del lateral debe marcarse activo. */
function isUserProfilePath(currentUrl: string): boolean {
    const path = normalizePath(currentUrl);

    return path === '/admin/profile' || path.startsWith('/admin/profile/');
}

function pathMatches(currentUrl: string, href: string): boolean {
    if (isUserProfilePath(currentUrl)) {
        return false;
    }

    const path = normalizePath(currentUrl);
    const target = normalizePath(href);

    if (path === target) {
        return true;
    }

    return target !== '/' && path.startsWith(`${target}/`);
}

function childrenOf(item: AsideNavItem): Array<{ slug: string; label: string; href: string }> {
    return item.functionalities ?? [];
}

function isDirectLink(item: AsideNavItem): boolean {
    const children = childrenOf(item);

    if (item.href && children.length === 0) {
        return true;
    }

    return children.length === 1;
}

function directHref(item: AsideNavItem): string {
    const children = childrenOf(item);

    if (item.href && children.length === 0) {
        return item.href;
    }

    return children[0]?.href ?? '#';
}

function directLabel(item: AsideNavItem): string {
    const children = childrenOf(item);

    if (item.href && children.length === 0) {
        return item.label;
    }

    const only = children[0];

    return item.slug === 'dashboard' ? item.label : (only?.label ?? item.label);
}

function activeGroupSlugs(menu: AsideNavItem[], currentUrl: string): Set<string> {
    return new Set(
        menu
            .filter(
                (item) =>
                    !isDirectLink(item) &&
                    childrenOf(item).some((child) => pathMatches(currentUrl, child.href)),
            )
            .map((item) => item.slug),
    );
}

function itemIsActive(item: AsideNavItem, currentUrl: string): boolean {
    const children = childrenOf(item);

    if (item.href && children.length === 0) {
        return pathMatches(currentUrl, item.href);
    }

    return children.some((child) => pathMatches(currentUrl, child.href));
}

function ExpandedNav({ menu }: { menu: AsideNavItem[] }) {
    const { url: currentUrl } = usePage();
    const autoOpen = useMemo(() => activeGroupSlugs(menu, currentUrl), [menu, currentUrl]);

    const [userOpen, setUserOpen] = useState<{
        url: string;
        map: Record<string, boolean>;
    }>({ url: currentUrl, map: {} });

    const manualMap = userOpen.url === currentUrl ? userOpen.map : {};

    const isExpanded = (slug: string): boolean =>
        Object.hasOwn(manualMap, slug) ? Boolean(manualMap[slug]) : autoOpen.has(slug);

    const toggleGroup = (slug: string): void => {
        const next = !isExpanded(slug);
        setUserOpen({
            url: currentUrl,
            map: {
                ...(userOpen.url === currentUrl ? userOpen.map : {}),
                [slug]: next,
            },
        });
    };

    return (
        <nav className="flex-1 space-y-0.5 overflow-y-auto p-3">
            {(menu ?? []).map((item) => {
                const Icon = resolveMenuIcon(item.icon);

                if (isDirectLink(item)) {
                    const href = directHref(item);
                    const active = pathMatches(currentUrl, href);

                    return (
                        <Link
                            key={item.slug}
                            href={href}
                            className={active ? primaryActive : primaryIdle}
                            aria-current={active ? 'page' : undefined}
                        >
                            <Icon className="size-4 shrink-0" aria-hidden />
                            <span className="truncate">{directLabel(item)}</span>
                        </Link>
                    );
                }

                const expanded = isExpanded(item.slug);

                return (
                    <div key={item.slug}>
                        <button
                            type="button"
                            className={primaryIdle}
                            aria-expanded={expanded}
                            onClick={() => toggleGroup(item.slug)}
                        >
                            <Icon className="size-4 shrink-0" aria-hidden />
                            <span className="min-w-0 flex-1 truncate text-left">{item.label}</span>
                            <ChevronDown
                                className={cn(
                                    'size-4 shrink-0 text-slate-500 transition-transform',
                                    expanded ? 'rotate-0' : '-rotate-90',
                                )}
                                aria-hidden
                            />
                        </button>
                        {expanded ? (
                            <ul className="mt-0.5 space-y-0.5 pb-1">
                                {childrenOf(item).map((child) => {
                                    const active = pathMatches(currentUrl, child.href);

                                    return (
                                        <li key={child.slug}>
                                            <Link
                                                href={child.href}
                                                className={active ? subActive : subIdle}
                                                aria-current={active ? 'page' : undefined}
                                            >
                                                {child.label}
                                            </Link>
                                        </li>
                                    );
                                })}
                            </ul>
                        ) : null}
                    </div>
                );
            })}
        </nav>
    );
}

function RailNav({ menu }: { menu: AsideNavItem[] }) {
    const { url: currentUrl } = usePage();
    const [openSlug, setOpenSlug] = useState<string | null>(null);

    return (
        <nav className="flex flex-1 flex-col items-center gap-1 overflow-y-auto px-1 py-2">
            {(menu ?? []).map((item) => {
                const Icon = resolveMenuIcon(item.icon);
                const active = itemIsActive(item, currentUrl);

                if (isDirectLink(item)) {
                    const href = directHref(item);
                    const label = directLabel(item);

                    return (
                        <Link
                            key={item.slug}
                            href={href}
                            title={label}
                            aria-label={label}
                            className={active ? railButtonActive : railButton}
                            aria-current={active ? 'page' : undefined}
                        >
                            <Icon className="size-5" aria-hidden />
                        </Link>
                    );
                }

                return (
                    <Popover
                        key={item.slug}
                        open={openSlug === item.slug}
                        onOpenChange={(open) => setOpenSlug(open ? item.slug : null)}
                    >
                        <PopoverTrigger asChild>
                            <button
                                type="button"
                                title={item.label}
                                aria-label={item.label}
                                className={active ? railButtonActive : railButton}
                            >
                                <Icon className="size-5" aria-hidden />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent
                            side="right"
                            align="start"
                            sideOffset={8}
                            className="w-52 p-1"
                        >
                            <p className="px-2 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {item.label}
                            </p>
                            <ul className="space-y-0.5">
                                {childrenOf(item).map((child) => {
                                    const childActive = pathMatches(currentUrl, child.href);

                                    return (
                                        <li key={child.slug}>
                                            <Link
                                                href={child.href}
                                                className={cn(
                                                    'block rounded-md px-2 py-1.5 text-sm',
                                                    childActive
                                                        ? 'bg-slate-100 font-medium text-slate-900'
                                                        : 'text-slate-700 hover:bg-slate-50',
                                                )}
                                                aria-current={childActive ? 'page' : undefined}
                                                onClick={() => setOpenSlug(null)}
                                            >
                                                {child.label}
                                            </Link>
                                        </li>
                                    );
                                })}
                            </ul>
                        </PopoverContent>
                    </Popover>
                );
            })}
        </nav>
    );
}

export default function AsideNav({
    menu,
    rail = false,
}: {
    menu: AsideNavItem[];
    rail?: boolean;
}) {
    return rail ? <RailNav menu={menu} /> : <ExpandedNav menu={menu} />;
}
