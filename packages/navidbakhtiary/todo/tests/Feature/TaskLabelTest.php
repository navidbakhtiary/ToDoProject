<?php

namespace NavidBakhtiary\ToDo\Tests\Feature;

use App\Models\User;
use NavidBakhtiary\ToDo\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NavidBakhtiary\ToDo\Config\HttpStatus;
use NavidBakhtiary\ToDo\Models\Label;
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
            assertJsonStructure(['data' => ['task label' => ['task' => ['user' => ['id', 'name'], 'title', 'description', 'status'], 'new label' => ['id', 'name']]]]);
        $this->assertDatabaseHas('task_label', ['task_id' => $task->id, 'label_id' => $label->id]);
    }

    public function testAuthenticatedUserCanNotAddLabelToOtherUserTask()
    {
        $user_1 = factory(User::class)->create();
        $user_2 = factory(User::class)->create();
        $token_1 = $user_1->createToken('test-token');
        $task_2 = $user_2->tasks()->create(factory(Task::class)->make()->toArray());
        $label = factory(Label::class)->create();
        $existed_records_count = $task_2->labels()->count();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token_1->plainTextToken])->
            postJson($this->api_add, ['task_id' => $task_2->id, 'label_id' => $label->id]);
        $response->assertStatus(HttpStatus::BadRequest)->
            assertJsonFragment(['errors' => ['task_id' => ['The selected task id is invalid.']]]);
        $this->assertTrue($existed_records_count == $task_2->labels()->count());
    }
}
