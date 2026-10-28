## 1. Componente + AppLayout

- [x] 1.1 Crear `PageBreadcrumb` (option Link + suboption texto; tipografía option grande /
      suboption menor; i18n via `t(optionKey)` / `t(suboptionKey)`)
- [x] 1.2 Extender `AppLayout` con prop `breadcrumb: { optionKey, suboptionKey, optionHref }`;
      render bajo topbar; `<Head>` derivado o `title` override
- [x] 1.3 Añadir fila en `docs/standards/frontend.md` catálogo

## 2. Migración páginas AppLayout

- [x] 2.1 Dashboard + Profile: breadcrumb (Index-like, suboption `dashboard`)
- [x] 2.2 Admin Modules / Functionalities (Index/Create/Edit + forms que envuelven layout)
- [x] 2.3 Admin Roles + CompanyRoles (Index/Create/Edit)
- [x] 2.4 Admin CompanyModules + CompanyModuleAuthorizations
- [x] 2.5 Partners Companies (Index/Create/Edit/Members)
- [x] 2.6 Completar claves i18n es/en/ca faltantes (snake_case); quitar subtítulos ad hoc
      duplicados del breadcrumb (mantener CTAs Create en children)

## 3. Tests y calidad

- [x] 3.1 Feature/smoke: Index modules muestra breadcrumb con Dashboard; Edit muestra
      suboption y option enlaza al index
- [x] 3.2 E2E: N/A
- [x] 3.3 Pint / PHPStan (si toca PHP) / tsc+lint front en verde

## Vinculaciones

- V-01..V-20 / R-TEN-05 / module-map: N/A (chrome UI)
