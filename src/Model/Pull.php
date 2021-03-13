<?php

namespace Mon\Oversight\Model;


class Pull
{
   public $node_id;
   public $plugin_name;
   public $title;
   public $state;
   public $user_login;
   public $body;
   public $created_at;
   public $closed_at;
   public $merged_at;
   public $html_url;

   public function __construct($pullData, $plugin_name) {
       $this->node_id = $pullData['node_id'];
       $this->plugin_name = $plugin_name;
       $this->title = $pullData['title'];
       $this->state = $pullData['state'];
                          $this->user_login =  $pullData['user']['login'];
                           $this->body =  $pullData['body'];
                          $this->created_at =   $pullData['created_at'];
                          $this->closed_at =  $pullData['closed_at'];
                           $this->merged_at = $pullData['merged_at'];
                          $this->html_url =  $pullData['html_url'];
   }
}