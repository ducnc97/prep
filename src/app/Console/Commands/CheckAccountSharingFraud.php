<?php

namespace App\Console\Commands;

use App\Models\Fraud;
use App\Models\LogRequest;
use App\Services\CheckLocationFraudServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAccountSharingFraud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-account-sharing-fraud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->checkLocations();
            $this->checkFingerprints();
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    /**
     * Check fraud by location.
     */
    private function checkLocations()
    {
        $checkLocationFraudService = app(CheckLocationFraudServiceInterface::class);
        $now = now();

        // Check location within 1 hour by request
        $startTimeYesterday = now()->subDay()->startOfDay();
        for ($i = 0; $i < 25; $i++) {
            $startTime = $startTimeYesterday->copy()->startOfHour();
            $endTime = $startTime->copy()->endOfHour();
            $logRequestByTimes = LogRequest::where('created_at', '>=', $startTime)->where('created_at', '<=', $endTime)->get();

            // Check fraud by group user
            $logRequestByUsers = $logRequestByTimes->groupBy('user_id');
            foreach ($logRequestByUsers as $userId => $logRequest) {
                $frauds = [];

                // Check for fraud between 2 locations
                foreach ($logRequest as $key1 => $logRequest1) {
                    foreach ($logRequest as $key2 => $logRequest2) {
                        if ($key1 == $key2) continue;

                        $params = [
                            'lat1' => $logRequest1['request']['lat'],
                            'long1' => $logRequest1['request']['long'],
                            'time1' => $logRequest1->created_at,
                            'lat2' => $logRequest2['request']['lat'],
                            'long2' => $logRequest2['request']['long'],
                            'time2' => $logRequest2->created_at,
                        ];
                        $isFraud = $checkLocationFraudService->isFraud($params);
                        if (!$isFraud) continue;

                        $frauds[] = [
                            'user_id' => $userId,
                            'type' => Fraud::TYPE_LOCATION,
                            'evidence' => json_encode([
                                'log_request_1' => $logRequest1->toArray(),
                                'log_request_2' => $logRequest2->toArray(),
                            ]),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                Fraud::insert($frauds);
            }
        }
    }

    /**
     * Check fraud by fingerprints.
     */
    private function checkFingerprints()
    {
        $this->checkFingerprintByUserAgent();
    }

    /**
     * Check fraud by fingerprint user-agent.
     */
    private function checkFingerprintByUserAgent()
    {
        // Check location within 1 hour by request
        $now = now();
        $startTimeYesterday = now()->subDay()->startOfDay();
        $endTimeYesterday = now()->subDay()->endOfDay();
        $logRequestByTimes = LogRequest::where('created_at', '>=', $startTimeYesterday)->where('created_at', '<=', $endTimeYesterday)->get();

        // Check fraud by group access_token
        $logRequestByAccessTokens = $logRequestByTimes->groupBy('access_token');
        foreach ($logRequestByAccessTokens as $logRequestByAccessToken) {
            $frauds = [];

            // Check for fraud between 2 user-agent
            foreach ($logRequestByAccessToken as $key1 => $logRequest1) {
                foreach ($logRequestByAccessToken as $key2 => $logRequest2) {
                    if ($key1 == $key2 || $logRequest1->request['user-agent'] == $logRequest2->request['user-agent']) continue;

                    $frauds[] = [
                        'user_id' => $logRequest1->user_id,
                        'type' => Fraud::TYPE_FINGERPRINTS_USER_AGENT,
                        'evidence' => json_encode([
                            'log_request_1' => $logRequest1->toArray(),
                            'log_request_2' => $logRequest2->toArray(),
                        ]),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            Fraud::insert($frauds);
        }
    }
}
