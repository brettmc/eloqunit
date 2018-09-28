<?php
namespace Eloqunit\Constraints;

class IsNotNull
{
    public function __toString()
    {
        return 'Not Null constraint';
    }
}
