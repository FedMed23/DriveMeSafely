<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Smarty/StartSmarty.php';

if (Installation::verificaInstallazione()) {
    $fcontroller = new CFrontController();
    $fcontroller->run($_SERVER['REQUEST_URI']);
} else {
    Installation::begin();
}
