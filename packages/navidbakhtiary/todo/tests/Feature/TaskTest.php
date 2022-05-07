<?php

namespace NavidBakhtiary\ToDo\Tests\Feature;

use App\Models\User;
use NavidBakhtiary\ToDo\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NavidBakhtiary\ToDo\Config\HttpStatus;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private $api_add = '/todo/tasks/add';
    private $bearer_prefix = 'Bearer ';

    public function testCreateTaskByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->postJson($this->api_add, $task->toArray());
        $response->assertCreated()->assertJsonStructure(['data' => ['task' => ['user' => ['id', 'name'], 'title', 'description', 'status']]]);
        $this->assertDatabaseHas('tasks', array_merge(['user_id' => $user->id], $task->toArray()));
    }

    public function testAuthenticatedUserCanNotCreateTaskUsingInvalidInputData()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['description' => $task->description, 'status' => 'Unknown']);
        $response->assertStatus(HttpStatus::BadRequest)->
            assertJsonFragment(['title' => ["The title field is required."], 'status' => ['The selected status is invalid.']]);
        $this->assertDatabaseMissing('tasks', ['user_id' => $user->id, 'title' => null, 'status' => 'Unknown']);
    }

}
