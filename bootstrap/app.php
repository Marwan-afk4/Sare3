<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$defaultStoragePath = dirname(__DIR__) . '/storage';
$tempStoragePath = null;

// If storage is not writable (e.g. Apache with ProtectHome=read-only), redirect to a writable path in /tmp
if (php_sapi_name() !== 'cli' && (!is_writable($defaultStoragePath) || !is_writable($defaultStoragePath . '/framework/views') || !is_writable($defaultStoragePath . '/logs'))) {
    $tempStoragePath = '/tmp/laravel-storage-' . md5(dirname(__DIR__));
    if (!is_dir($tempStoragePath)) {
        mkdir($tempStoragePath, 0777, true);
        chmod($tempStoragePath, 0777);
    }
    foreach (['framework/views', 'framework/cache', 'framework/sessions', 'logs', 'app/public', 'app/private'] as $subdir) {
        $path = $tempStoragePath . '/' . $subdir;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            chmod($path, 0777);
        }
    }
}

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web([
            //sanctum middleware enures authenticated first
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // Custom middleware should come after authentication
            //\App\Http\Middleware\RoleMiddleware::class,
            \App\Http\Middleware\UpdateLastVisit::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

if ($tempStoragePath !== null) {
    $app->useStoragePath($tempStoragePath);
}

return $app;
