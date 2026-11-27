import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

export type YearCalendarDay = {
    date: string;
    name: string;
    color?: string | null;
};

type Props = {
    year: number;
    days: YearCalendarDay[];
    onDayClick?: (date: string, day: YearCalendarDay | null) => void;
    onYearChange?: (year: number) => void;
    readOnly?: boolean;
};

function dateKey(year: number, month: number, day: number): string {
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

function daysInMonth(year: number, month: number): number {
    return new Date(year, month, 0).getDate();
}

/** Monday=0 … Sunday=6 for the 1st of month. */
function leadingBlanks(year: number, month: number): number {
    const jsDay = new Date(year, month - 1, 1).getDay(); // Sun=0

    return jsDay === 0 ? 6 : jsDay - 1;
}

export default function YearCalendarView({
    year,
    days,
    onDayClick,
    onYearChange,
    readOnly = false,
}: Props) {
    const { i18n } = useTranslation();

    const byDate = useMemo(() => {
        const map = new Map<string, YearCalendarDay>();
        for (const day of days) {
            map.set(day.date, day);
        }

        return map;
    }, [days]);

    const monthNames = useMemo(() => {
        const formatter = new Intl.DateTimeFormat(i18n.language, { month: 'long' });

        return Array.from({ length: 12 }, (_, index) =>
            formatter.format(new Date(year, index, 1)),
        );
    }, [i18n.language, year]);

    const weekdayLabels = useMemo(() => {
        // Monday-first short labels via Intl (Mon=1 … Sun=0 mapped).
        const formatter = new Intl.DateTimeFormat(i18n.language, { weekday: 'narrow' });
        // 2024-01-01 was Monday.
        return Array.from({ length: 7 }, (_, index) =>
            formatter.format(new Date(2024, 0, 1 + index)),
        );
    }, [i18n.language]);

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between gap-3">
                <button
                    type="button"
                    className="rounded-md border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50"
                    onClick={() => onYearChange?.(year - 1)}
                    disabled={!onYearChange}
                >
                    ← {year - 1}
                </button>
                <h2 className="text-lg font-semibold text-slate-900">{year}</h2>
                <button
                    type="button"
                    className="rounded-md border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50"
                    onClick={() => onYearChange?.(year + 1)}
                    disabled={!onYearChange}
                >
                    {year + 1} →
                </button>
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {monthNames.map((monthName, monthIndex) => {
                    const month = monthIndex + 1;
                    const blanks = leadingBlanks(year, month);
                    const total = daysInMonth(year, month);

                    return (
                        <section
                            key={month}
                            className="rounded-xl border border-slate-200 bg-white p-3"
                        >
                            <h3 className="mb-2 text-sm font-semibold capitalize text-slate-800">
                                {monthName}
                            </h3>
                            <div className="grid grid-cols-7 gap-0.5 text-center text-[10px] text-slate-500">
                                {weekdayLabels.map((label) => (
                                    <div key={`${month}-${label}`} className="py-1 font-medium">
                                        {label}
                                    </div>
                                ))}
                            </div>
                            <div className="grid grid-cols-7 gap-0.5">
                                {Array.from({ length: blanks }).map((_, index) => (
                                    <div key={`b-${month}-${index}`} />
                                ))}
                                {Array.from({ length: total }).map((_, index) => {
                                    const dayNum = index + 1;
                                    const key = dateKey(year, month, dayNum);
                                    const marked = byDate.get(key) ?? null;
                                    const clickable = !readOnly && Boolean(onDayClick);

                                    return (
                                        <button
                                            key={key}
                                            type="button"
                                            disabled={!clickable}
                                            title={marked?.name ?? key}
                                            onClick={() => onDayClick?.(key, marked)}
                                            className={[
                                                'flex aspect-square items-center justify-center rounded text-xs',
                                                marked
                                                    ? marked.color
                                                        ? 'font-semibold text-white'
                                                        : 'font-semibold text-slate-800 ring-1 ring-slate-300 bg-slate-100'
                                                    : 'text-slate-700 hover:bg-slate-100',
                                                clickable ? 'cursor-pointer' : 'cursor-default',
                                            ].join(' ')}
                                            style={
                                                marked?.color
                                                    ? { backgroundColor: marked.color }
                                                    : undefined
                                            }
                                        >
                                            {dayNum}
                                        </button>
                                    );
                                })}
                            </div>
                        </section>
                    );
                })}
            </div>
        </div>
    );
}
