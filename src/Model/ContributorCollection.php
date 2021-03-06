<?php

namespace Mon\Oversight\Model;

use Mon\Oversight\inc\Services;

class ContributorCollection
{

    public $contributorCollection = [];

    function __construct($url, $plugin_name)
    {
        $contributorsArray = Services::getApiData($url, '');

        foreach ($contributorsArray as $contributor) {
            $contributorData = Services::getApiData($contributor['url'], '');

            $this->contributorCollection[] = new Contributor($contributorData, $plugin_name);
        }
    }

    /**
     * @return array
     */
    public function getContributorCollection()
    {
        return $this->contributorCollection;
    }


}