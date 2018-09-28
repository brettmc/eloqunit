<?php
namespace Eloqunit;
use Illuminate\Database\Capsule\Manager;

interface EloqunitInterface
{
    public function getDatabase(): Manager;
}
