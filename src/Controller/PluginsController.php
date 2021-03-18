<?php

namespace Mon\Oversight\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

use Mon\Oversight\inc\DB;

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
        if (isset($plugins) && array_key_exists('Error', $plugins)) {
            $message = $plugins['Error'];
            return $this->twig->render($response, 'error.twig', ['pageName' => 'Error', 'message' => $message]);
        }
        return $this->twig->render($response, 'table.twig', ['pageName' => 'Plugins', 'data' => $plugins]);
    }
}