<?php

namespace NavidBakhtiary\ToDo\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use NavidBakhtiary\ToDo\Models\Task;
use NavidBakhtiary\ToDo\Models\User;
use NavidBakhtiary\ToDo\Notifications\TaskStatusClosed;
use NavidBakhtiary\ToDo\Responses\BadRequestResponse;
use NavidBakhtiary\ToDo\Responses\CreatedResponse;
use NavidBakhtiary\ToDo\Responses\OkResponse;
use NavidBakhtiary\ToDo\Responses\UnprocessableEntityResponse;
use NavidBakhtiary\ToDo\Rules\UserTaskExistenceRule;

class TaskController extends Controller
{
    public function details($id)
    {
        $validation = Validator::make(['task_id' => $id], [
            'task_id' => ['required', 'integer', new UserTaskExistenceRule()]
        ]);
        if ($validation->fails()) {
            return BadRequestResponse::sendErrors($validation->errors()->messages());
        }
        $task = Task::with(['labels' => function ($query) {
                $query->withCount('userTasks');
            }])->find($id);
        return OkResponse::sendTaskDetails($task);
    }

    public function index()
    {
        $user = new User(Auth::user());
        $tasks = $user->tasks()->with(['labels' => function($query){
                $query->withCount('userTasks');
            }])->get();
        return OkResponse::sendUserTasks($tasks);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string|max:500', 
            'status' => 'in:' . Task::$status_open . ',' . Task::$status_close,
        ]);
        if ($validation->fails()) 
        {
            return BadRequestResponse::sendErrors($validation->errors()->messages());
        }
        $user = new User(Auth::user());
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

    public function statusSwitching(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'task_id' => ['required', 'integer', new UserTaskExistenceRule()]
        ]);
        if ($validation->fails()) {
            return BadRequestResponse::sendErrors($validation->errors()->messages());
        }
        $task = Task::find($request->task_id);
        $new_status = ($task->status == Task::$status_open) ? Task::$status_close : Task::$status_open;
        if ($task->update(['status' => $new_status])) {
            if ($new_status == Task::$status_close) {
                $task->notify(new TaskStatusClosed());
            }
            return CreatedResponse::sendTask($task);
        }
        return UnprocessableEntityResponse::sendMessage();
    }
}
