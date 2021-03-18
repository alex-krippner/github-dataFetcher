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
        if (isset($issues) && array_key_exists('Error', $issues)) {
            $message = $issues['Error'];
            return $this->twig->render($response, 'error.twig', ['pageName' => 'Error', 'message' => $message]);
        }
        return $this->twig->render($response, 'table.twig', ['pageName' => 'Issues', 'data' => $issues]);
    }
}