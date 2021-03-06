<?php


use Slim\App;

// FIXME: Add groups

return function (App $app) {
    $app->get('/', \Mon\Oversight\Controller\HomeController::class . ':renderHomeView');
    $app->get('/plugins', \Mon\Oversight\Controller\PluginsController::class . ':getPlugins');
    $app->get('/admin', \Mon\Oversight\Controller\AdminController::class . ':renderRowCount');
    $app->get('/admin/update', \Mon\Oversight\Controller\AdminController::class . ':insertData');
    $app->get('/contributors', \Mon\Oversight\Controller\ContributorsController::class . ':showTable');
    $app->get('/issues', \Mon\Oversight\Controller\IssuesController::class . ':showTable');
    $app->get('/report', \Mon\Oversight\Controller\ReportController::class . ':getReport');
    $app->get('/pull-requests', \Mon\Oversight\Controller\PullsController::class . ':getPulls');
    $app->get('/report/all-plugins', \Mon\Oversight\Controller\ReportController::class . ':getAggregatePluginReport');
};

