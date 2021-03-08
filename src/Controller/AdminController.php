<?php

namespace Mon\Oversight\Controller;

use Mon\Oversight\inc\DB;
use Mon\Oversight\Model\ContributorCollection;
use Mon\Oversight\Model\PluginCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;


class AdminController
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function renderRowCount(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $db = new DB();
        $db->connect();
        $count = $db->countRows('plugins');
        $db->closeConnection();

        return $this->twig->render($response, 'admin.twig', ['count' => $count, 'table_name' => 'plugins']);
    }


    public function insertData(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // connect to DB
        $db = new DB();
        $db->connect();

        // get plugin repos in array form
        $pluginCollection = new PluginCollection();
        $plugins = $pluginCollection->getPlugins(2);

        // loop array of plugin objects and insert into database's plugins table
        if (isset($plugins)) {
            foreach ($plugins as $plugin) {
                $query = "
                INSERT INTO plugins (plugin_name, owner_login, open_issues_count, forks_count, commits_count )
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT (plugin_name) DO UPDATE SET
                    owner_login=excluded.owner_login,
                    open_issues_count=excluded.open_issues_count,
                    forks_count=excluded.forks_count,
                    commits_count=excluded.commits_count
                ";
                $data = [$plugin->name, 'cosmocode', $plugin->open_issues_count, $plugin->fork, $plugin->commits];

                // insert plugin data
                $db->insertData($query, $data);


                $contributors = null;
                // insert contributors data
                if (isset($_GET) && isset($_GET['get'])) {
                    $contributors = preg_match('/^contributors$/', $_GET['get']);
                }
// FIXME:  Change contributors table's primary key to contributor_login
                if ($contributors) {
                    // get contributors in array form
                    $contributorCollection = new ContributorCollection($plugin->contributors_api_url, $plugin->name);
                    $contributors = $contributorCollection->getContributorCollection();
                    foreach ($contributors as $contributor) {
                        $query = "
                        INSERT INTO contributors (plugin_name, contributor_login, name, email, company)
                        VALUES (?, ?, ?, ?, ?)
                        ON CONFLICT (plugin_name) DO UPDATE SET
                            contributor_login=excluded.contributor_login,
                            name=excluded.name,
                            email=excluded.email,
                            company=excluded.company
                ";

                        $data = [
                            $contributor->plugin_name,
                            $contributor->contributor_login,
                            $contributor->name,
                            $contributor->email,
                            $contributor->company,
                        ];

                        // insert plugin data
                        $db->insertData($query, $data);
                    }
                }
            }
        }
        $count = $db->countRows('plugins');
        $db->closeConnection();
        return $this->twig->render($response, 'admin.twig', ['count' => $count, 'table_name' => 'plugins']);
    }
}