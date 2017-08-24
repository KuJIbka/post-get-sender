<?php

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

date_default_timezone_set('Europe/Moscow');

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
