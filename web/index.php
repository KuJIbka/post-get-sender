<?php

mb_internal_encoding('utf-8');
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['baseDir'] = dirname(__DIR__);
$app['my.config'] = require_once $app['baseDir'].'/app/config/Config.php';
$app['debug'] = $app['my.config']['debug'];

# Registers -------------------------------
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src',
));

# Routers -------------------------------
require_once __DIR__.'/../app/config/Routers.php';

$app->run();
gc_collect_cycles();
