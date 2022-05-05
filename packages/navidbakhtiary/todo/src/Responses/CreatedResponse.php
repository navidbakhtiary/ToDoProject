<?php

namespace NavidBakhtiary\ToDo\Responses;

use NavidBakhtiary\ToDo\Config\HttpStatus;
use NavidBakhtiary\ToDo\Resources\DataResource;
use NavidBakhtiary\ToDo\Resources\LabelResource;

class CreatedResponse extends Response
{
    public static function sendLabel($label)
    {
        return self::send(
            HttpStatus::Created,
            new DataResource([
                'label' => new LabelResource($label)
            ]), 
        );
    }
}
