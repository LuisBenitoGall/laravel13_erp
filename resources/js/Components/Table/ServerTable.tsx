import { useEffect, useRef } from 'react';
import { TabulatorFull as Tabulator, type ColumnDefinition } from 'tabulator-tables';
import '../../../css/tabulator-erp.css';

type ServerTableProps = {
    endpoint: string;
    columns: ColumnDefinition[];
};

export default function ServerTable({ endpoint, columns }: ServerTableProps) {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!ref.current) {
            return;
        }

        const table = new Tabulator(ref.current, {
            layout: 'fitColumns',
            pagination: true,
            paginationMode: 'remote',
            paginationSize: 25,
            ajaxURL: endpoint,
            ajaxConfig: 'GET',
            columns,
            ajaxResponse: (_url, _params, response) => ({
                data: response.data,
                last_page: response.last_page,
            }),
        });

        return () => {
            table.destroy();
        };
    }, [endpoint, columns]);

    return <div ref={ref} className="tabulator-erp" />;
}
