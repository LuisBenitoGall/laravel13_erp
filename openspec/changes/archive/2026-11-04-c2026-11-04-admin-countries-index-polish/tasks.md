# Tasks

## 1. ServerTable Acciones

- [x] 1.1 Formatter: renderizar `action.icon` Lucide vía `renderToStaticMarkup`;
      danger default trash si no hay icon; verificar visualmente Map ≠ Pencil
- [x] 1.2 Columna Acciones: `widthGrow: 0` + ancho por slots (techo ≥4 o prop);
      verificar Index Países sin hueco vacío amplio
- [x] 1.3 Smoke regresivo: Index Modules Acciones (toggle/edit/delete) siguen
      operativos tras el cambio de ancho/iconos

## 2. Indexes geográficos

- [x] 2.1 Countries Index: quitar columna Estado; icon drill `Map`; Edit `Pencil`
- [x] 2.2 Provinces Index: quitar columna Estado; icon drill `MapPin`; Edit `Pencil`
- [x] 2.3 Towns Index: quitar columna Estado; Acciones ancho coherente (sin drill)

## 3. Docs / calidad

- [x] 3.1 Nota breve en ficha Admin (Indexes geo sin columna Estado; iconos Map/
      MapPin) si aplica
- [x] 3.2 Feature tests: no exigir columna Estado en asserts de UI si los hubiera;
      suite tocada verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).
