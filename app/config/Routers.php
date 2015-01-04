<?php

$app->get('/', 'Main\Controller\DefaultController::index')->bind('homePage');
$app->post('/getAnswer', 'Main\Controller\DefaultController::getAnswer')->bind('getAnswer');
$app->get('/loadPresets', 'Main\Controller\DefaultController::loadPresets')->bind('loadPresets');
$app->post('/savePreset', 'Main\Controller\DefaultController::savePreset')->bind('savePreset');
$app->match('/deletePreset', 'Main\Controller\DefaultController::deletePreset')->bind('deletePreset');

$app->match('/test/headers', 'Test\Controller\DefaultController::index')->bind('testHeaders1');
$app->match('/test/headers2', 'Test\Controller\DefaultController::index2')->bind('testHeaders2');
$app->match('/test/headers3', 'Test\Controller\DefaultController::index3')->bind('testHeaders3');
