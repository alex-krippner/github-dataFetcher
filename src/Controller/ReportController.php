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
        if (!isset($reportData) || empty($reportData)) {
            $message = 'No report data available';
            return $this->twig->render($response, 'error.twig', ['pageName' => 'Error', 'message' => $message]);
        }
        return $this->twig->render($response, 'report.twig', ['pageName' => 'Report', 'data' => $reportData[0]]);
    }

    public function getAggregatePluginReport(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $db = new DB();
        $db->connect();
        $pluginAggregateReportData = $db->getAggregatePluginReportData();

        if (!isset($pluginAggregateReportData) || empty($pluginAggregateReportData)) {
            $message = 'No report data available';
            return $this->twig->render($response, 'error.twig', ['pageName' => 'Error', 'message' => $message]);
        }

        return $this->twig->render($response, 'aggregateReport.twig',
            ['pageName' => 'Aggregate Report', 'data' => $pluginAggregateReportData[0]]);
    }
}