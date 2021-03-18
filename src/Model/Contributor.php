<?php

namespace Mon\Oversight\Model;

class Contributor
{

    public $plugin_name;
    public $contributor_login;
    public $name;
    public $email;
    public $company;
    public $contributions;
    public $contributionId;


    public function __construct($contributorData, $plugin_name, $contributions)
    {
        $this->plugin_name = $plugin_name;
        $this->contributor_login = $contributorData['login'];
        $this->name = $contributorData['name'];
        $this->email = $contributorData['email'];
        $this->company = $contributorData['company'];
        $this->contributions = $contributions;
        $this->contributionId = $plugin_name . ' ' . $this->contributor_login;
    }
}