export interface DayTypeRow {
    id: number;
    name: string;
    color: string;
    is_working: boolean;
    created_at?: string | null;
    updated_at?: string | null;
}

export interface WorkCalendarDayRow {
    id: number;
    date: string;
    name: string;
    day_type_id: number;
    day_type?: {
        id: number;
        name: string;
        color: string;
        is_working: boolean;
    };
}

export interface WorkCalendarPayload {
    id: number;
    workplace_id: number;
    company_id: number;
}

export interface IdNameOption {
    id: number;
    name: string;
}
