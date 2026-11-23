export interface ContractTypeRow {
    id: number;
    name: string;
    slug?: string;
    duration: number | null;
    with_holidays: boolean;
    status: 'active' | 'inactive';
    created_at?: string | null;
    updated_at?: string | null;
}

export type EmployeeStatus = 'scheduled' | 'active' | 'ended';

export interface EmployeeRow {
    id: number;
    user_id: number;
    user_name: string | null;
    contract_type_id: number;
    contract_type_name: string | null;
    workplace_id: number | null;
    workplace_name: string | null;
    job_title: string | null;
    start_date: string;
    end_date: string | null;
    weekly_hours: string;
    time_control: boolean;
    year_salary: string | null;
    status: EmployeeStatus;
    observations: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    can?: {
        update: boolean;
        renew: boolean;
        end: boolean;
        delete: boolean;
    };
}

export interface IdNameOption {
    id: number;
    name: string;
}

export interface ContractTypeOption {
    id: number;
    name: string;
    /** null = indefinido */
    duration: number | null;
}

export interface EmployeeOptions {
    users: IdNameOption[];
    contract_types: ContractTypeOption[];
    workplaces: IdNameOption[];
}
