<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\dummyPost;
use Config;

class dymmyJobPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'http:dummy-post-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a dummy POST call for testing purposes using a job queue';

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
        //
        dummyPost::dispatch();
        $this->info(Config::get('constants.post.message'));
    }
}
