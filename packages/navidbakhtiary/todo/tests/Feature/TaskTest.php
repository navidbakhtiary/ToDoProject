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
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, $task->toArray());
        dd($response); 
        $response->assertCreated()->
            assertJsonStructure(['data' => ['task' => ['user' => ['id', 'name'], 'title', 'description', 'status']]]);
        $this->assertDatabaseHas('tasks', array_merge(['user_id' => $user->id], $task->toArray()));
    }
}
