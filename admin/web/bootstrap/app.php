<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

// vendor/ is a junction to ../../web/vendor, so its autoloader maps App\ to
// /web/app. Prepend this app's own app/ directory so the admin classes that
// live in this folder take precedence, with /web/app still reachable behind it.
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('App\\', [__DIR__.'/../app'], true);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php')
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson(),
        );
    })
    ->create();
