<?php

namespace Mon\Oversight\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

use Mon\Oversight\inc\DB;

class PullsController
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function getPulls(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $db = new DB();
        $db->connect();
        $query = "SELECT * FROM pulls ORDER BY plugin_name DESC";
        $pulls = $db->queryDB($query);
        $db->closeConnection();
        if (count($pulls) === 0) {
            echo 'No pull requests found';
            die();
        }
        return $this->twig->render($response, 'table.twig', ['pageName' => 'Pull Requests', 'data' => $pulls]);
    }
}