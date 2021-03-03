<?php

namespace oversight\model;

use model\Plugin as Plugin;

class PluginCollection
{

    public $pluginsData = [];
    public $allRepoData;

    /**
     * Model constructor. Initializes JSON demo data.
     */

    function __construct()
    {
        $this->allRepoData = file_get_contents('./data/repos.json');
        $this->allRepoData = json_decode($this->allRepoData, true);
    }

    /**
     * Loops through an array of repos, instantiates a Plugin class for each
     * repo, and pushes the new Plugin object into an array.
     *
     * @return array
     */

    public function getPlugins()
    {
        foreach ($this->allRepoData as $k => $repo) {
            if ($k > 9) {
                break;
            }
            $plugin = $repo['name'];
            $this->pluginsData[] = new Plugin($repo);
        }

        return $this->pluginsData;
    }


}