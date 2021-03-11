<?php


use Slim\App;

// FIXME: Add groups
// TODO: Add twig nav block

return function (App $app) {
    $app->get('/', \Mon\Oversight\Controller\HomeController::class . ':renderHomeView');
    $app->get('/plugins', \Mon\Oversight\Controller\PluginsController::class . ':getPlugins');
    $app->get('/admin', \Mon\Oversight\Controller\AdminController::class . ':renderRowCount');
    $app->get('/admin/update', \Mon\Oversight\Controller\AdminController::class . ':insertData');
    $app->get('/contributors', \Mon\Oversight\Controller\ContributorsController::class . ':showTable');
};

