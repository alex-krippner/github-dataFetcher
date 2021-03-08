<?php


use Slim\App;

return function (App $app) {
    $app->get('/plugins', \Mon\Oversight\Controller\PluginsController::class . ':getPlugins');
    $app->get('/admin', \Mon\Oversight\Controller\AdminController::class . ':renderRowCount');
    $app->get('/admin/update', \Mon\Oversight\Controller\AdminController::class . ':insertData');
};

