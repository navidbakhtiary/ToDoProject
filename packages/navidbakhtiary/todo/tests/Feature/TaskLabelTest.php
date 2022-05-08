<?php

namespace NavidBakhtiary\ToDo\Tests\Feature;

use App\Models\User;
use NavidBakhtiary\ToDo\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NavidBakhtiary\ToDo\Config\HttpStatus;
use Tests\TestCase;

class TaskLabelTest extends TestCase
{
    use RefreshDatabase;

    private $api_add = '/todo/task/label/add';
    private $bearer_prefix = 'Bearer ';

    public function testAddLabelToTaskByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label = factory(Label::class)->create();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['task_id' => $task->id, 'label_id' => $label->id]);
        $response->assertCreated()->
            assertJsonStructure(['data' => ['task' => ['user' => ['id', 'name'], 'title', 'description', 'status', 'new label' => ['id', 'name']]]]);
        $this->assertDatabaseHas('task_label', ['task_id' => $task->id, 'label_id' => $label->id]);
    }
}
