<?php

namespace Mon\Oversight\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

use Mon\Oversight\inc\DB;

// TODO: Add documnetation

class HomeController
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function renderHomeView(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $db = new DB();
        $db->connect();
        $db->createTable();
        $db->closeConnection();
        return $this->twig->render($response, 'home.twig');
    }
}