<?php
require __DIR__ . '/../vendor/autoload.php';

use Eloqunit\App;

$app = App::create();
$app->run(false);
