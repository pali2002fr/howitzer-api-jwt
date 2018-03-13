<?php
require '../vendor/autoload.php';
// Run app
$app = (new App\App(__DIR__.'/../'))->get();
$app->run();