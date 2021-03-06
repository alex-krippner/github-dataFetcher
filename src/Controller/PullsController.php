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
        if (isset($pulls) && array_key_exists('Error', $pulls)) {
            $message = $pulls['Error'];
            return $this->twig->render($response, 'error.twig', ['pageName' => 'Error', 'message' => $message]);
        }
        return $this->twig->render($response, 'table.twig', ['pageName' => 'Pull Requests', 'data' => $pulls]);
    }
}