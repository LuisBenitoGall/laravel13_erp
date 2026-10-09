import { useForm, usePage } from '@inertiajs/react';

type CompanyOption = {
    id: number;
    name: string;
    slug: string;
    tradename: string | null;
};

export default function SelectCompany({ companies }: { companies: CompanyOption[] }) {
    const { errors } = usePage().props as { errors: Record<string, string> };
    const form = useForm({ company_id: companies[0]?.id ?? 0 });

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
            <div className="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h1 className="text-lg font-semibold text-slate-900">Seleccionar empresa</h1>
                <p className="mt-1 text-sm text-slate-600">
                    Elige la empresa con la que quieres trabajar.
                </p>
                <form
                    className="mt-4 space-y-3"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post('/companies/select');
                    }}
                >
                    <label className="block text-sm font-medium text-slate-700" htmlFor="company_id">
                        Empresa
                    </label>
                    <select
                        id="company_id"
                        className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        value={form.data.company_id}
                        onChange={(e) => form.setData('company_id', Number(e.target.value))}
                    >
                        {companies.map((company) => (
                            <option key={company.id} value={company.id}>
                                {company.tradename || company.name}
                            </option>
                        ))}
                    </select>
                    {errors.company_id && (
                        <p className="text-sm text-red-600">{errors.company_id}</p>
                    )}
                    <button
                        type="submit"
                        className="w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800"
                        disabled={form.processing}
                    >
                        Continuar
                    </button>
                </form>
            </div>
        </div>
    );
}
