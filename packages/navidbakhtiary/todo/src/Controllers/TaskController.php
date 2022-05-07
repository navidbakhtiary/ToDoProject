<?php

namespace NavidBakhtiary\ToDo\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use NavidBakhtiary\ToDo\Responses\BadRequestResponse;
use NavidBakhtiary\ToDo\Responses\CreatedResponse;
use NavidBakhtiary\ToDo\Responses\UnprocessableEntityResponse;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string|max:500', 
            'status' => 'in:Open,Close',
        ]);
        if ($validation->fails()) 
        {
            return BadRequestResponse::sendErrors($validation->errors()->messages());
        }
        $user = Auth::user();
        if($task = $user->tasks()->create($request->all()))
        {
            return CreatedResponse::sendTask($task);
        }
        return UnprocessableEntityResponse::sendMessage();
    }
}
