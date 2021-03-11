<?php

namespace Mon\Oversight\Controller;

use Mon\Oversight\inc\DB;
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
        $db = new DB();
        $db->connect();
        $query = 'SELECT * FROM plugins ORDER BY open_issues_count DESC ';
        $plugins = $db->queryDB($query);
        $db->closeConnection();
        if (count($plugins) === 0) {
            echo 'No Plugins';
            die();
        }
        return $this->twig->render($response, 'pluginsTable.html.twig', ['plugins' => $plugins]);
    }
}