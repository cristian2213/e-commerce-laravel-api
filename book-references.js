/*
- ROUTE CACHING:
    - When the routes is cached, i can make changes in my routes, but the changes aren't going to be reflected until i execute the first commant again, this is useful in "production":

        1- php artisan route:cache
        2- php artisan route:clear


- ABORTING THE REQUEST:
    - abort(403, 'Message error!')
    - abort_unless($request->has('magicToken), 403)
    - abort_if($request->user()->isBanned, 403)

- WORKING WITH FILES:
    - response()->download()
    - response()->file() => To show in the browser when it is downloaded.
    - response()->streamDownload(callback)
*/
