# ERP v2 — Boost no sustituye el kernel

This application is **Inertia v3 + React 19**, not Livewire, not Blade CRUD, not yajra DataTables.

If any Boost guideline or skill contradicts `docs/standards/kernel.md` or architecture `R-*`, **follow the kernel**. Boost is for Laravel/Inertia/Pest/Tailwind **APIs**, not tenancy, Module/Functionality, or menu.

- Use `CurrentCompany`, never `session('currentCompany')`.
- Domain code lives in `app/Domains/<Dominio>/`.
- Listings: `<ServerTable>` (Tabulator).
- Permissions: `<functionality>.<acción>`.
- Do not write business invariants with Boost `Record Rule` (`.ai/rules` is disabled).
