<?php

namespace Mon\Oversight\Controller;

use Mon\Oversight\inc\DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class ReportController
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function getReport(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $plugin_name = $_REQUEST['search'];

        $db = new DB();
        $db->connect();
        $reportData = $db->getReportData($plugin_name);
        $db->closeConnection();
        if (!isset($reportData)) {
            echo "Couldn't produce report";
            die();
        }
        return $this->twig->render($response, 'report.twig', ['pageName' => 'Report', 'data' => $reportData]);
    }
}