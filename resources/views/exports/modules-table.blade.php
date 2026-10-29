<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ __('modulos') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h1 { font-size: 16px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: 600; }
    </style>
</head>
<body>
    <h1>{{ __('modulos') }}</h1>
    <table>
        <thead>
            <tr>
                <th>{{ __('nombre') }}</th>
                <th>{{ __('color') }}</th>
                <th>{{ __('icono') }}</th>
                <th>{{ __('nivel') }}</th>
                <th>{{ __('creado') }}</th>
                <th>{{ __('estado') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->icon }}</td>
                    <td>{{ $row->level->value }}</td>
                    <td>{{ $row->created_at?->toDateTimeString() }}</td>
                    <td>{{ $row->status->value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
