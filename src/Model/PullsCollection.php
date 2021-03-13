<?php

namespace Mon\Oversight\Model;

use Mon\Oversight\Model\Pull as Pull;

class PullsCollection
{

    public $pullsCollection = [];

    function __construct($pullsArray, $plugin_name)
    {

        foreach ($pullsArray as $pullData) {
            $this->pullsCollection[] = new Pull($pullData, $plugin_name);
        }
    }

    /**
     * @return array
     */
    public function getPullsCollection()
    {
        return $this->pullsCollection;
    }


}