<?php

namespace Mon\Oversight\Model;

use Mon\Oversight\inc\Services;

class IssuesCollection
{

    public $issuesCollection = [];

    function __construct($url, $plugin_name)
    {
        $issuesArray = Services::getApiData($url, '');

        foreach ($issuesArray as $issueData) {
            $this->issuesCollection[] = new Issue($issueData, $plugin_name);
        }
    }

    /**
     * @return array
     */
    public function getIssuesCollection()
    {
        return $this->issuesCollection;
    }


}