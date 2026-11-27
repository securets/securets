<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;

require_once __DIR__.'/php85-carbon-compat.php';

// vendor/ is a junction to ../../web/vendor, so its autoloader maps App\ to
// /web/app. Prepend this app's own app/ directory so the admin classes that
// live in this folder take precedence, with /web/app still reachable behind it.
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('App\\', [__DIR__.'/../app'], true);

// The optimized classmap resolves App\ entries relative to the junction
// (admin/web/app), where shared classes such as App\Models\User don't exist.
// Point those entries at the real /web/app files.
$sharedAppPath = dirname(__DIR__, 3).'/web/app';
$sharedClasses = [];
foreach ($loader->getClassMap() as $class => $file) {
    if (str_starts_with($class, 'App\\') && ! is_file($file)) {
        $shared = $sharedAppPath.'/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (is_file($shared)) {
            $sharedClasses[$class] = $shared;
        }
    }
}
$loader->addClassMap($sharedClasses);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php')
    ->withMiddleware(function (Middleware $middleware): void {
        // The admin mobile/web Expo client runs on a different origin.
        // Allow it to read and submit the admin API requests.
        $middleware->append(HandleCors::class);

        // The admin mobile app logs in with credentials and receives a bearer
        // token; it has no session, so it cannot send a CSRF token.
        $middleware->validateCsrfTokens(except: ['admin/api/login', 'admin/api/logout']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson(),
        );
    })
    ->create();
