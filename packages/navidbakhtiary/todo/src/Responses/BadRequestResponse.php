<?php

namespace NavidBakhtiary\ToDo\Responses;

use NavidBakhtiary\ToDo\Resources\ErrorResource;
use NavidBakhtiary\ToDo\Config\HttpStatus;

class BadRequestResponse extends Response
{
    public static function sendErrors($errors)
    {
        return self::send(
            HttpStatus::BadRequest,
            new ErrorResource($errors),
        );
    }
}
