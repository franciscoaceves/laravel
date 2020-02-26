<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Exception;
use Log;
use Config;

class dummyPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->tries = Config::get('constants.queue.tries');
        $this->retryAfter = Config::get('constants.queue.retry_after');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $client = new Client();

        $response = $client->post(Config::get('constants.post.uri'));
        $code = $response->getStatusCode();

        if ($code == 201) {
            Log::info("POST success: $code");
        } else {
            Log::error("Unexpected server response: $code");
        }
    }

    public function failed(Exception $e)
    {
        Log::error("Error in server response");
        Log::error(Psr7\str($e->getResponse()));
    }
}
