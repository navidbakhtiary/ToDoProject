<?php

namespace NavidBakhtiary\ToDo\Controllers;

use App\Rules\UserTaskExistence;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use NavidBakhtiary\ToDo\Models\Task;
use NavidBakhtiary\ToDo\Responses\BadRequestResponse;
use NavidBakhtiary\ToDo\Responses\CreatedResponse;
use NavidBakhtiary\ToDo\Responses\UnprocessableEntityResponse;
use NavidBakhtiary\ToDo\Rules\UserTaskExistenceRule;

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

    public function update(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'task_id' => ['required', 'integer', new UserTaskExistenceRule()],
            'title' => 'required|string',
            'description' => 'required|string|max:500'
        ]);
        if ($validation->fails()) 
        {
            return BadRequestResponse::sendErrors($validation->errors()->messages());
        }
        $task = Task::find($request->task_id);
        if ($task->update($request->except(['task_id']))) 
        {
            return CreatedResponse::sendTask($task);
        }
        return UnprocessableEntityResponse::sendMessage();
    }
}
