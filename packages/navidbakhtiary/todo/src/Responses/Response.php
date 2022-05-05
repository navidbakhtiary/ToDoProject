<?php

namespace NavidBakhtiary\ToDo\Responses;

class Response
{
    public static function send($status, $result)
    {
        return response($result, $status)
            ->header('Content-Type', 'application/json');
    }
}
