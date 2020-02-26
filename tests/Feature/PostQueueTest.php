<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Jobs\dummyPost;
use Illuminate\Support\Facades\Queue;
use Config;

class PostQueue extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_artisan()
    {
        Queue::fake();

        $this->artisan(' http:dummy-post-queue')
            ->expectsOutput(Config::get('constants.post.message'));
            
        Queue::assertPushed(dummyPost::class);
    }
}
