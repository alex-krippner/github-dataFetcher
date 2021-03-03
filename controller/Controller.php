<?php

namespace oversight\controller;

use oversight\inc\DB;
use oversight\model\PluginCollection;

class Controller
{


    /**
     * Initiates a new plugin collection and displays plugin data
     */
    public function showPlugins()
    {
        $pluginCollection = new PluginCollection();
        $plugins = $pluginCollection->getPlugins(1);
        if (count($plugins) === 0) {
            echo 'No Plugins';
            die();
        }
        include __DIR__ . '/../view/pluginsTable.php';
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
        $plugins = $pluginCollection->getPlugins(4);

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

                $db->insertData($query, $data);
            }
        }

        $db->countRows('plugins');
        $db->closeConnection();
    }
}