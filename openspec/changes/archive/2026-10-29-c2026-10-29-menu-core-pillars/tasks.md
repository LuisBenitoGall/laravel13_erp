## 1. Catálogo / seed / i18n

- [x] 1.1 Label i18n Mis empresas (+ pilar Empresas) para functionality `companies`
- [x] 1.2 Seed idempotente Functionality `permissions` (admin/AdminOnly) → href Roles
- [x] 1.3 Seed idempotente Functionality `company-users` (workspace/Basic o grupo Usuarios)
      → Members company sesión
- [x] 1.4 Claves i18n es/en/ca (`empresas`, `mis_empresas`, `usuarios`, `usuarios_empresa`,
      `permisos`, …)

## 2. MenuBuilder

- [x] 2.1 Grupos `empresas` / `usuarios`; sacar `companies` del bloque Configuración SA
- [x] 2.2 Configuración SA: orden/filtro priorizando modules, roles, permissions
      (+ functionalities si design lo mantiene)
- [x] 2.3 `hrefFor`: `permissions` → `admin.roles.index`; `company-users` →
      `partners.companies.members.index` con CurrentCompany
- [x] 2.4 Feature: SA ve Configuración + Permisos→roles; no-SA no Configuración; Mis
      empresas label; Usuarios empresa → members

## 3. Calidad / docs

- [x] 3.1 Nota breve en Admin.md / ficha: pilares menú; CRUD permisos custom = futuro
- [x] 3.2 E2E: N/A
- [x] 3.3 Pint / PHPStan / tests menú en verde

## 4. Correcciones 25-architecture-review

- [x] 4.1 Members (index + updateRole) fuera de `module:admin` → `module:workspace`
- [x] 4.2 Authorize index/update con `company-users.viewAny` / `company-users.update` + vínculo company
- [x] 4.3 Feature: no-SA y SA GET Members → 200; regresiones pilares

## Vinculaciones

- V-09 catálogo: sí (functionalities nuevas en seed)
- R-TEN-05: N/A entidad nueva; perímetro companies intacto
- Pantallas nuevas: N/A
