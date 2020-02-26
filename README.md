# 'POST' request command

A useful approach to send several async POST requests is using queued jobs. 
The following command will add a job to the queue which, when dispatched, will attempt a POST request using a Guzzle client. It is set to retry max 3 times with a 10s delay (these settings can be changed in the constants-config-file).
The artisan command:
```
php artisan  http:dummy-post-queue
```

Queues requires at least one connection provider (db, sqs, redis). In a scenario when we don't want to use a dedicated queue connection, a simpler (and limited) alternative is possible, using Guzzle and a connection pool.
The artisan command:
```
php artisan  http:dummy-post-bare
```
The advantage with queues is tha the retry logic is already there and we have at our disposal several events we can listen to.
I'm including the second command just as an example of an alternative. The best approach for most scenarios is, however, a queue provider

