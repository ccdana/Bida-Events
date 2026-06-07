<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $invitation->title }}</title>
<style>body{font-family:DejaVu Sans,sans-serif;text-align:center;padding:40px}h1{font-size:24px;color:#C9A96E}p{color:#666;margin:8px 0}</style>
</head><body>
<h1>{{ $modulos['bienvenida']['nombre_quinceanera'] ?? $invitation->title }}</h1>
<p>{{ $modulos['bienvenida']['subtitulo'] ?? '' }}</p>
<p>{{ $modulos['bienvenida']['fecha_texto'] ?? $invitation->event_date->format('d/m/Y') }}</p>
<p>{{ $modulos['bienvenida']['mensaje'] ?? '' }}</p>
<p style="margin-top:30px;font-size:10px;color:#999">Diseñado con ♡ por Bida-Events</p>
</body></html>
