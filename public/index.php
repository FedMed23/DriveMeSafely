<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Smarty/StartSmarty.php';

use CamassoMedelago\DriveMeSafely\Controller\CFrontController;

$fcontroller = new CFrontController();
$fcontroller->run($_SERVER['REQUEST_URI']);
