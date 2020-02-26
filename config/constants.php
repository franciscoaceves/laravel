<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Constants
    |--------------------------------------------------------------------------
    |
    */
        'queue' => [
                'tries' => 3,
                'retry_after' => 10,
                'concurrency' => 3
        ],

        'post' => [
            'uri' => 'https://atomic.incfile.com',
            'message' => 'Job added to the queue'
        ]
    ];
