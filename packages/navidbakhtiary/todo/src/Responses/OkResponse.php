<?php

namespace NavidBakhtiary\ToDo\Responses;

use NavidBakhtiary\ToDo\Config\HttpStatus;
use NavidBakhtiary\ToDo\Resources\DataResource;
use NavidBakhtiary\ToDo\Resources\LabelResource;
use NavidBakhtiary\ToDo\Resources\LabelsIndexResource;
use NavidBakhtiary\ToDo\Resources\TaskDetailsResource;
use NavidBakhtiary\ToDo\Resources\UserTasksIndexResource;

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

    public static function sendTaskDetails($task)
    {
        return self::send(
            HttpStatus::Ok,
            new DataResource([
                'task' => new TaskDetailsResource($task)
            ]),
        );
    }

    public static function sendUserTasks($tasks)
    {
        return self::send(
            HttpStatus::Ok,
            new DataResource([
                'tasks' => UserTasksIndexResource::collection($tasks)
            ]),
        );
    }
}
