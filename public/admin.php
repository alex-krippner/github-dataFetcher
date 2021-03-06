<?php

use mon\oversight\controller\Controller;

require __DIR__ . '/../vendor/autoload.php';


$controller = new Controller();
$controller->initDB();
$controller->insertData();