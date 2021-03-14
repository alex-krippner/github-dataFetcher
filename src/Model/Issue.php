<?php

namespace Mon\Oversight\Model;

class Issue
{

    public $node_id;
    public $plugin_name;
    public $title;
    public $body;
    public $state;
    public $issue_number;
    public $user_login;
    public $created_at;
    public $closed_at;


    public function __construct($issueData, $plugin_name)
    {
        $this->node_id = $issueData['node_id'];
        $this->plugin_name = $plugin_name;
        $this->title = $issueData['title'];
        $this->body = $issueData['body'];
        $this->state = $issueData['state'];
        $this->issue_number = $issueData['number'];
        $this->user_login = $issueData['user']['login'];
        $this->created_at = date('Y-m-d H:m', strtotime($issueData['created_at']));
        $this->closed_at = date('Y-m-d H:m', strtotime($issueData['closed_at']));
    }
}