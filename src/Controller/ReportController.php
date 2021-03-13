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
        /**
         * TODO: STATS TO GET
         *
         * CODE STATS:
         *  AVERAGE COMMITS PER YEAR -- DONE
         *  COMMITS IN THE LAST TWO YEARS -- OMIT FOR NOW
         *
         * CODE STATS:
         *  OPEN PR -- DONE
         *  CLOSED PR -- DONE
         *
         * COMMUNITY STATS
         *  CONTRIBUTORS COUNT -- DONE
         *  FORKS -- DONE
         *  STARS -- DONE
         *  WATCHERS -- DONE
         *  OPEN ISSUES -- DONE
         *  CLOSED ISSUES -- DONE
         *  OPENED IN THE LAST TWO YEARS
         *  CLOSED IN TEH LAST TWO YEARS
         *
         *
         *
         *
         */

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