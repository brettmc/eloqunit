<?php
namespace Eloqunit;

use Eloqunit\Constraints\IsNull;
use Eloqunit\Constraints\IsNotNull;

class Constraint
{
    public static function IsNull(): IsNull
    {
        return new IsNull();
    }

    public static function IsNotNull(): IsNotNull
    {
        return new IsNotNull();
    }
}
