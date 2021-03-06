<?php

namespace mon\oversight\controller;

use mon\oversight\inc\DB;
use mon\oversight\inc\Services;
use mon\oversight\model\PluginCollection;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;


// TODO: Create class model for contributor
// TODO: Set twig cache to off

class Controller
{


    /**
     * Initiates a new plugin collection and displays plugin data
     */
    public function showPlugins()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../view');
        $twig = new Environment($loader);
        $twig->addExtension(new \Twig\Extension\DebugExtension());

        $twig->addFilter(new \Twig\TwigFilter('cast_to_array', function ($dataArray) {
            $response = array();
            $classArray = array();
            foreach ($dataArray as $stdClassObject) {
                foreach ($stdClassObject as $key => $value) {
                    $classArray = array_merge($classArray, array($key => $value));
                }
                $response[] = $classArray;
            }

            return $response;
        }));

        $twig->addFilter(new \Twig\TwigFilter('classObj_to_array', function ($stdClassObject) {
            $classArray = array();

            foreach ($stdClassObject as $key => $value) {
                $classArray = array_merge($classArray, array($key => $value));
            }

            return $classArray;
        }));

        $pluginCollection = new PluginCollection();
        $plugins = $pluginCollection->getPlugins(1);
        if (count($plugins) === 0) {
            echo 'No Plugins';
            die();
        }
        echo $twig->render('pluginsTable.html.twig', ['plugins' => $plugins]);
    }


    /**
     * Initializes a database by creating a connection to a database, creating a table, and inserting initial data
     */
    public function initDB()
    {
        // instantiate database
        $db = new DB();
        $db->connect();

        // create table
        $db->createTable();
        $db->closeConnection();
    }

    public function insertData()
    {
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
                    $service = new Services();
                    $contributorsArray = $service->getApiData($plugin->contributors_api_url, '');
                    foreach ($contributorsArray as $contributor) {
                        $contributorData = $service->getApiData($contributor['url'], '');
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
                            $plugin->name,
                            $contributorData['login'],
                            $contributorData['name'],
                            $contributorData['email'],
                            $contributorData['company'],
                        ];

                        // insert plugin data
                        $db->insertData($query, $data);
                    }
                }
            }
        }

        $db->countRows('plugins');
        $db->closeConnection();
    }
}