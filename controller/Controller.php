<?php

namespace oversight\controller;

use oversight\inc\DB;

class Controller
{


    /**
     * Initiates a new plugin collection and displays plugin data
     */
    public function showPlugins()
    {
        $pluginCollection = new \oversight\model\PluginCollection();
        $plugins = $pluginCollection->getPlugins();
        include __DIR__ . '/../view/pluginsTable.php';
    }


    /**
     * Initializes a database by creating a connection to a database, creating a table, and inserting initial data
     */
    public function initDB()
    {
        $allRepoData = file_get_contents(__DIR__ . '/../data/repos.json');
        $allRepoData = json_decode($allRepoData, true);


        // instantiate database
        $db = new DB();
        $db->connect();

        // create table
        $db->createTable();


        // loop the json data and insert into plugins table
        if (isset($allRepoData)) {
            foreach ($allRepoData as $repo) {
                $query = "INSERT INTO plugins (plugin_name, owner_login, open_issues_count, forks_count ) 
                VALUES (?, ?, ?, ?);";
                $data = [$repo['name'], $repo['owner']['login'], $repo['open_issues'], $repo['forks']];

                $db->insertData($query, $data);
            }
        }

        $db->countRows('plugins');
    }
}