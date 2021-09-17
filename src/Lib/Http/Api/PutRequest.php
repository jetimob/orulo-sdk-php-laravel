<?php

namespace Jetimob\Orulo\Lib\Http\Api;

use Jetimob\Orulo\Lib\Http\Method;
use Jetimob\Orulo\Lib\Http\Request;

abstract class PutRequest extends Request
{
    public function method(): string
    {
        return Method::PUT;
    }
}
