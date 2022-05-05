<?php

namespace NavidBakhtiary\ToDo\Responses;

use NavidBakhtiary\ToDo\Config\Error;
use NavidBakhtiary\ToDo\Config\HttpStatus;
use NavidBakhtiary\ToDo\Resources\ErrorResource;

class UnprocessableEntityResponse extends Response
{
    public static function sendMessage()
    {
        return self::send(
            HttpStatus::UnprocessableEntity,
            new ErrorResource([Error::UnprocessableEntity])
        );
    }
}
