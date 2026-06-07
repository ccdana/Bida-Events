<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Confirmados — {{ $invitation->title }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#333}h1{font-size:18px}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f5f5f5}</style>
</head><body>
<h1>{{ $invitation->title }}</h1>
<p>Fecha: {{ $invitation->event_date->format('d/m/Y H:i') }}</p>
<table>
    <thead><tr><th>Nombre</th><th>Estado</th><th>Pases</th><th>Restricciones</th></tr></thead>
    <tbody>
        @foreach($guests as $guest)
        <tr>
            <td>{{ $guest->name }}</td>
            <td>{{ $guest->status }}</td>
            <td>{{ $guest->passes_confirmed }}/{{ $guest->passes_allocated }}</td>
            <td>{{ $guest->dietary_restrictions ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body></html>
