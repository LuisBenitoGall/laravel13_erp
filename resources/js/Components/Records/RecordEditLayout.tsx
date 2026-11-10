import { type ReactNode, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { cn } from '@/lib/utils';

export type RecordEditTab = {
    id: string;
    labelKey: string;
    /** Default true. Hidden tabs are omitted from the tablist. */
    visible?: boolean;
    panel: ReactNode;
};

export type RecordEditLayoutProps = {
    /** Cabecera (p. ej. RecordSummary). */
    summary?: ReactNode;
    tabs: RecordEditTab[];
    /** Controlled active tab id; uncontrolled if omitted. */
    activeTab?: string;
    onTabChange?: (id: string) => void;
    className?: string;
};

export default function RecordEditLayout({
    summary,
    tabs,
    activeTab: controlledActive,
    onTabChange,
    className,
}: RecordEditLayoutProps) {
    const { t } = useTranslation();
    const visibleTabs = useMemo(
        () => tabs.filter((tab) => tab.visible !== false),
        [tabs],
    );

    const firstId = visibleTabs[0]?.id ?? '';
    const [uncontrolledActive, setUncontrolledActive] = useState(firstId);
    const activeId = controlledActive ?? uncontrolledActive;
    const activeTab = visibleTabs.find((tab) => tab.id === activeId) ?? visibleTabs[0];

    function selectTab(id: string) {
        if (controlledActive === undefined) {
            setUncontrolledActive(id);
        }
        onTabChange?.(id);
    }

    return (
        <div className={cn('w-full', className)} data-testid="record-edit-layout">
            {summary}

            {visibleTabs.length > 0 ? (
                <>
                    <div role="tablist" className="flex flex-wrap gap-1 border-b border-slate-200">
                        {visibleTabs.map((tab) => {
                            const selected = tab.id === activeTab?.id;

                            return (
                                <button
                                    key={tab.id}
                                    type="button"
                                    role="tab"
                                    aria-selected={selected}
                                    className={cn(
                                        'border-b-2 px-3 py-2 text-sm',
                                        selected
                                            ? 'border-teal-700 font-medium text-teal-800'
                                            : 'border-transparent text-slate-600',
                                    )}
                                    onClick={() => selectTab(tab.id)}
                                >
                                    {t(tab.labelKey)}
                                </button>
                            );
                        })}
                    </div>

                    <div role="tabpanel" className="pt-4" data-testid={`record-edit-panel-${activeTab?.id ?? ''}`}>
                        {activeTab?.panel}
                    </div>
                </>
            ) : null}
        </div>
    );
}
