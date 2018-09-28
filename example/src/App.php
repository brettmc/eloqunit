<?php
namespace App;

use Slim\App as Slim;

class App
{
    public static function create(): Slim
    {
        $settings = require __DIR__ . '/settings.php';
        $app = new Slim($settings);
        require(__DIR__.'/routes.php');
        require(__DIR__.'/dependencies.php');
        return $app;
    }
}
