<?php

namespace Mon\Oversight\Controller;

use Mon\Oversight\inc\DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class IssuesController
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function showTable(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $db = new DB();
        $db->connect();
        $query = 'SELECT * FROM issues ORDER BY plugin_name ASC, created_at ASC ';
        $issues = $db->queryDB($query);
        $db->closeConnection();
        if (count($issues) === 0) {
            echo 'No Plugins';
            die();
        }
        return $this->twig->render($response, 'table.twig', ['pageName' => 'Contributors', 'data' => $issues]);
    }
}