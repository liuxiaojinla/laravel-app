<?php
return [
//    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class . ':admin',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
];
