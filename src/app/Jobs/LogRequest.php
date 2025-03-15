<?php

namespace App\Jobs;

use App\Models\LogRequest as ModelsLogRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogRequest implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Request $request,
        public Response $response,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            ModelsLogRequest::create(
                [
                    'user_id' => Auth::id() ?? null,
                    'ip' => $this->request->ip(),
                    'access_token' => $this->response->original['data']['access_token'],
                    'request' => [
                        'ip' => $this->request->ip(),
                        'user-agent' => $this->request->userAgent(),
                        'lat' => $this->request->header('lat'),
                        'long' => $this->request->header('long'),
                    ],
                ]
            );
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}
