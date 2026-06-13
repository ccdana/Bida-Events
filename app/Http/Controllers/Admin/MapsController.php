<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MapsLinkParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapsController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Accept-Language' => 'es',
                    'User-Agent' => 'BidaEvents/1.0 (admin geocoder)',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format'         => 'jsonv2',
                    'limit'          => 8,
                    'addressdetails' => 1,
                    'namedetails'    => 1,
                    'extratags'      => 1,
                    'q'              => $validated['q'],
                ]);

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo consultar el buscador de direcciones.',
                ], 502);
            }

            $results = collect($response->json())
                ->map(fn (array $item) => [
                    'id' => (string) ($item['place_id'] ?? md5(json_encode($item))),
                    'label' => $item['display_name'] ?? '',
                    'short_label' => self::shortLabel($item),
                    'lat' => (float) ($item['lat'] ?? 0),
                    'lng' => (float) ($item['lon'] ?? 0),
                    'type' => $item['type'] ?? null,
                ])
                ->filter(fn (array $item) => $item['lat'] && $item['lng'])
                ->values();

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Tiempo de espera agotado al buscar la dirección.',
            ], 504);
        }
    }

    public function resolve(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $url = trim($validated['url']);
        $finalUrl = $url;
        $coords = MapsLinkParser::parse($url);

        if (! $coords) {
            $finalUrl = $this->resolveRedirectUrl($url);
            $coords = MapsLinkParser::parse($finalUrl);
        }

        if (! $coords) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudieron extraer coordenadas del enlace. Prueba con el enlace completo desde Google Maps.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'lat' => $coords['lat'],
            'lng' => $coords['lng'],
            'maps_url' => MapsLinkParser::buildUrl($coords['lat'], $coords['lng']),
            'resolved_url' => $finalUrl ?? $url,
        ]);
    }

    protected function resolveRedirectUrl(string $url): string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'BidaEvents/1.0 (maps resolver)'])
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->get($url);

            return (string) ($response->effectiveUri() ?? $url);
        } catch (\Throwable) {
            return $url;
        }
    }

    protected static function shortLabel(array $item): string
    {
        $address  = $item['address'] ?? [];
        $nameDetails = $item['namedetails'] ?? [];
        $extraTags   = $item['extratags'] ?? [];

        // Intentar obtener el nombre del lugar/establecimiento
        $venueName = $nameDetails['name'] ?? $nameDetails['name:es'] ?? null;

        // Algunas claves de OSM que indican un establecimiento con nombre propio
        $venueKeys = [
            'amenity', 'leisure', 'tourism', 'building',
            'shop', 'office', 'historic', 'club',
        ];
        $isVenue = false;
        foreach ($venueKeys as $key) {
            if (! empty($address[$key]) || ! empty($extraTags[$key])) {
                $isVenue = true;
                break;
            }
        }

        // Partes de la dirección
        $street = $address['road'] ?? $address['pedestrian'] ?? $address['footway'] ?? null;
        $district = $address['suburb'] ?? $address['neighbourhood'] ?? $address['quarter'] ?? null;
        $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? null;

        $parts = array_filter([
            // Si es un lugar con nombre, ponerlo primero
            ($isVenue && $venueName) ? $venueName : null,
            $street,
            $district,
            $city,
        ]);

        // Si solo teníamos calle + ciudad (sin nombre de lugar), mostramos igual
        if (! $parts) {
            $parts = array_filter([$street, $district, $city]);
        }

        if ($parts) {
            return implode(', ', $parts);
        }

        $name = $item['display_name'] ?? '';

        return mb_strlen($name) > 72 ? mb_substr($name, 0, 69).'…' : $name;
    }
}
