<?php
$container = $app->getContainer();

$container['eloquent'] = function ($c) {
    $eloquent = new Illuminate\Database\Capsule\Manager();
    $eloquent->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
    $eloquent->setAsGlobal();
    // Boot eloquent
    $eloquent->bootEloquent();
    $eloquent->getConnection()->statement('create table foo (id string, value string, created_at timestamp default CURRENT_DATE, updated_at timestamp)');
    return $eloquent;
};
