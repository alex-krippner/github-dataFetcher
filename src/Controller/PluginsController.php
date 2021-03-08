<?php

namespace Mon\Oversight\Controller;

use Mon\Oversight\inc\Helper;
use Mon\Oversight\Model\PluginCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class PluginsController
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function getPlugins(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // TODO: Get plugins from database
        // Instead of getting data from github using the Models;
        // get data from the database with the DB class
        // TODO: write data fetcher method in the DB class
        $pluginCollection = new PluginCollection();
        $plugins = $pluginCollection->getPlugins(1);

        $this->twig->getEnvironment()->addFilter(new \Twig\TwigFilter('cast_to_array', function ($target) {
            return Helper::castToArray($target);
        }));
        if (count($plugins) === 0) {
            echo 'No Plugins';
            die();
        }
        return $this->twig->render($response, 'pluginsTable.html.twig', ['plugins' => $plugins]);
    }
}