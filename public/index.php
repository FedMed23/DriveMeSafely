<?php
require_once 'utility/autoload.php';
require_once 'StartSmarty.php';

if (Installation::verificaInstallazione()) {
    $fcontroller = new CFrontController();
    $fcontroller->run($_SERVER['REQUEST_URI']);
} else {
    Installation::begin();
}