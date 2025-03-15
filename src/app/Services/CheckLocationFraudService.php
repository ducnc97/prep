<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckLocationFraudService implements CheckLocationFraudServiceInterface
{
    /**
     * Check fraud.
     */
    public function checkFraud(Request $request): bool
    {
        $userId = Auth::id();
        $location = Cache::get('location_' . $userId);
        $params = [
            'lat1' => $location['lat'],
            'long1' => $location['long'],
            'time1' => $location['time'],
            'lat2' => $request->header('lat'),
            'long2' => $request->header('long'),
            'time2' => now(),
        ];

        if ($location != null) {
            return $this->isFraud($params);
        }

        $location = Cache::put('location_' . $userId, [
            'lat' => $request->header('lat'),
            'long' => $request->header('lat'),
            'time' => $request->header('time'),
        ]);

        return true;
    }

    /**
     * Is fraud by lat, long, time of 2 location.
     */
    public function isFraud(array $params): bool
    {
        $distance = $this->calculateDistance($params['lat1'], $params['long1'], $params['lat2'], $params['long2']);
        $v = $distance / abs($params['time2']->diffInHours($params['time1']));
        if ($distance > 0.05 * 2 && $v > 1000) {
            return true;
        }

        return false;
    }

    /**
     * Calculate distance 2 location.
     */
    private function calculateDistance($lat1, $long1, $lat2, $long2)
    {
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $long1 = deg2rad($long1);
        $lat2 = deg2rad($lat2);
        $long2 = deg2rad($long2);

        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $long2 - $long1;

        $a = sin($dlat / 2) * sin($dlat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dlon / 2) * sin($dlon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Earth's radius in kilometers (use 6371 for km)
        $R = 6371;

        // Calculate distance
        $distance = $R * $c;

        return $distance; // Distance in kilometers
    }
}
