<?php

namespace NavidBakhtiary\ToDo\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use NavidBakhtiary\ToDo\Models\Label;
use NavidBakhtiary\ToDo\Models\Task;
use NavidBakhtiary\ToDo\Responses\BadRequestResponse;
use NavidBakhtiary\ToDo\Responses\CreatedResponse;
use NavidBakhtiary\ToDo\Responses\UnprocessableEntityResponse;
use NavidBakhtiary\ToDo\Rules\TaskLabelNotExistenceRule;
use NavidBakhtiary\ToDo\Rules\TaskLabelUniqueRule;
use NavidBakhtiary\ToDo\Rules\UserTaskExistenceRule;

class TaskLabelController extends Controller
{
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'task_id' => ['required', 'integer', 'exists:tasks,id', new UserTaskExistenceRule()],
            'label_id' => ['required', 'integer', 'exists:labels,id', new TaskLabelUniqueRule($request->task_id)]
        ]);
        if ($validation->fails()) 
        {
            return BadRequestResponse::sendErrors($validation->errors()->messages());
        }
        $task = Task::find($request->task_id);
        $label = Label::find($request->label_id);
        if($task->labels()->syncWithoutDetaching($label->id))
        {
            return CreatedResponse::sendTaskLabel($task, $label);
        }
        return UnprocessableEntityResponse::sendMessage();
    }
}
