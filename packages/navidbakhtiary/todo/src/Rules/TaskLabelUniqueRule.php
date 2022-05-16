<?php

namespace NavidBakhtiary\ToDo\Rules;

use Illuminate\Contracts\Validation\Rule;
use NavidBakhtiary\ToDo\Models\Task;

class TaskLabelUniqueRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $task_id;

    public function __construct($task_id)
    {
        $this->task_id = $task_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(Task::find($this->task_id)->labels()->find($value))
        {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('The label has already been attached.');
    }
}
