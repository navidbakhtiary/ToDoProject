<?php

namespace NavidBakhtiary\ToDo\Responses;

use NavidBakhtiary\ToDo\Config\HttpStatus;
use NavidBakhtiary\ToDo\Resources\DataResource;
use NavidBakhtiary\ToDo\Resources\LabelResource;
use NavidBakhtiary\ToDo\Resources\LabelsIndexResource;

class OkResponse extends Response
{
    public static function sendLabels($labels)
    {
        return self::send(
            HttpStatus::Ok,
            new DataResource([
                'labels' => LabelsIndexResource::collection($labels)
            ]),
        );
    }
}
