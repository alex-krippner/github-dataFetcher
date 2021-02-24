<?php

namespace githubDataFetcher\controller;

class Controller
{
    public function invoke()
    {
        $pluginCollection = new \githubDataFetcher\model\PluginCollection();
        $plugins = $pluginCollection->getPlugins();
        include __DIR__ . '/../view/pluginsTable.php';
    }
}