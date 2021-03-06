<?php

namespace Mon\Oversight\Model;

use Mon\Oversight\Model\Plugin as Plugin;

// FIXME: Write getter method

class PluginCollection
{

    public $pluginsData = [];
    public $allRepoData;

    /**
     * Model constructor. Initializes JSON demo data.
     */

    function __construct()
    {
        $this->allRepoData = file_get_contents(__DIR__ . '/../data/repos.json');
        $this->allRepoData = json_decode($this->allRepoData, true);
    }

    /**
     * Loops through an array of repos, instantiates a Plugin class for each
     * repo, and pushes the new Plugin object into an array.
     *
     * @return array
     * @var number $amount The number (1 based / not array index based) of plugins for which data is to be fetched
     */

    public function getPlugins($amount)
    {
        if ($amount === 0) {
            return array();
        }

        foreach ($this->allRepoData as $k => $repo) {
            if ($k > $amount - 1) {
                break;
            }
            $this->pluginsData[] = new Plugin($repo);
        }

        return $this->pluginsData;
    }


}