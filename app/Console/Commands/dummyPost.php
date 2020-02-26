<?php

namespace App\Console\Commands;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Log;
use Config;

class dummyPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'http:dummy-post-bare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a dummy POST call for testing purposes without a queue mechanism';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
            Using a Retry middleware to enable complex retry logic:
            -Automatically retries HTTP requests when a server responds with a 404
            -Uses a retry delay
            -Optionally retries requests that time out
            -Calls a function when a retry occurs
            -Uses a maximum number of retry attempts before giving up
         */
        $callback = function($attemptNumber, $delay, &$request, &$options, $response) {
    
            echo sprintf(
                "Retrying(#%s) in %s seconds...  Server responded with %s.".PHP_EOL,
                $attemptNumber,
                number_format($delay, 2),
                $response->getStatusCode()
            );
        };

        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());

        /*
            Using Guzzle to handle the POST request logic:
            -Setting each call and each retry as asynchronous 
            -Using a pool of connections to handle concurrency
            -Log to file the results and show in screen
         */
        $client = new Client([
            'handler' => $stack,
            'retry_on_status' => [404],
            'max_retry_attempts' => Config::get('constants.queue.tries'),
            'on_retry_callback'  => $callback
            ]);

        $requests = function ($total) {
            $uri = Config::get('constants.post.uri');
            for ($i = 0; $i < $total; $i++) {
                yield new Request('POST', $uri);
            }
        };
        $this->info("here:");
        $pool = new Pool($client, $requests(20), [
            'concurrency' => Config::get('constants.queue.concurrency'),
            'fulfilled' => function (Response $response, $index) {
                $code = $response->getStatusCode();

                //The response code for a POST request should be 201. But many servers could send instead 200.
                if ($code == 201) {
                    $this->info("Success:");
                    $this->info($response->getBody());
                } else {
                    $this->error("Unexpected server response: $code");
                    Log::error("Unexpected server response: $code");
                }
            },
            'rejected' => function (RequestException $e) {
                if ($e->hasResponse()) {
                    Log::error('Error in POST:'.$e->getResponse()->getStatusCode());
                    Log::error(Psr7\str($e->getResponse()));
                    
                    $this->error("POST request failed with:" . $e->getResponse()->getStatusCode());
                }
            },
        ]);

        $promise = $pool->promise();

        /*
            After setting the connection pool, the promise can be used to start asynchronously processing the requests
         */
        $promise->wait();
        
    }
}
