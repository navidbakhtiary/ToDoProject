<?php

namespace NavidBakhtiary\ToDo\Responses;

use NavidBakhtiary\ToDo\Config\HttpStatus;
use NavidBakhtiary\ToDo\Resources\DataResource;
use NavidBakhtiary\ToDo\Resources\LabelResource;
use NavidBakhtiary\ToDo\Resources\TaskLabelResource;
use NavidBakhtiary\ToDo\Resources\TaskResource;

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

    public static function sendTask($task)
    {
        return self::send(
            HttpStatus::Created,
            new DataResource([
                'task' => new TaskResource($task)
            ]),
        );
    }

    public static function sendTaskLabel($task, $label)
    {
        return self::send(
            HttpStatus::Created,
            new DataResource([
                'task label' => ['task' => new TaskResource($task), 'new label' => new LabelResource($label)]
            ]),
        );
    }
}
