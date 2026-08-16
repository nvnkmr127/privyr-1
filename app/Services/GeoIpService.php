<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Resolve IP address to latitude, longitude, city, and country.
     *
     * @param string $ip
     * @return array
     */
    public function resolve(string $ip): array
    {
        // Localhost fallback
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return [
                'city' => 'Bengaluru',
                'region' => 'Karnataka',
                'country' => 'India',
                'lat' => 12.9716,
                'lon' => 77.5946,
            ];
        }

        try {
            $response = Http::get("http://ip-api.com/json/{$ip}");
            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'city' => $response->json('city', 'Unknown'),
                    'region' => $response->json('regionName', ''),
                    'country' => $response->json('country', 'India'),
                    'lat' => $response->json('lat', 12.9716),
                    'lon' => $response->json('lon', 77.5946),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('GeoIpService lookup error: ' . $e->getMessage());
        }

        return [
            'city' => 'New Delhi',
            'region' => 'Delhi',
            'country' => 'India',
            'lat' => 28.6139,
            'lon' => 77.2090,
        ];
    }
}
