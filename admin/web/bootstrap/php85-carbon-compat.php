<?php

declare(strict_types=1);

/**
 * Carbon versions before the PHP 8.5 compatibility fix declare
 * CarbonPeriod::getIterator() as Generator. PHP 8.5 requires the
 * DatePeriod-compatible Iterator return type.
 *
 * This loader is intentionally limited to the admin application. The admin
 * vendor directory is linked to the main application's vendor directory, so
 * changing the package in-place would affect both applications.
 */
spl_autoload_register(static function (string $class): void {
    if ($class !== 'Carbon\\CarbonPeriod') {
        return;
    }

    $carbonPeriod = __DIR__.'/../vendor/nesbot/carbon/src/Carbon/CarbonPeriod.php';
    $source = file_get_contents($carbonPeriod);

    if ($source === false) {
        throw new RuntimeException('Unable to load the admin CarbonPeriod compatibility class.');
    }

    $source = str_replace(
        'public function getIterator(): Generator',
        'public function getIterator(): \\Iterator',
        $source,
    );
    $source = str_replace(
        "__DIR__.'/../../lazy/Carbon/",
        "__DIR__.'/../vendor/nesbot/carbon/lazy/Carbon/",
        $source,
    );

    eval(substr($source, strpos($source, '<?php') + 5));
}, true, true);

// The shared package manifest contains Reverb, but Reverb is intentionally
// not installed for the standalone admin application. Keep those stale
// cached provider names from preventing the admin console from booting.
spl_autoload_register(static function (string $class): void {
    $providers = [
        'Laravel\\Reverb\\ApplicationManagerServiceProvider',
        'Laravel\\Reverb\\ReverbServiceProvider',
    ];

    if (! in_array($class, $providers, true)) {
        return;
    }

    $shortName = substr($class, strrpos($class, '\\') + 1);

    eval('namespace Laravel\\Reverb; class '.$shortName.' extends \\Illuminate\\Support\\ServiceProvider {}');
}, true, true);
