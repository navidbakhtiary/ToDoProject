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

    private $api_add = '/todo/task/add';
    private $api_edit = '/todo/task/edit';
    private $bearer_prefix = 'Bearer ';

    public function testCreateTaskByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, $task->toArray());
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

    public function testUnauthenticatedUserCanNotCreateTask()
    {
        $task = factory(Task::class)->make();
        $existed_records_count = Task::count();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . hash('sha256', 'fake token')])->
            postJson($this->api_add, $task->toArray());
        
        $response->assertUnauthorized();
        $this->assertTrue($existed_records_count == Task::count());
    }

    public function testAuthenticatedUserCanEditInformationOfOwnTask()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $alternative_task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_edit, array_merge(['task_id' => $task->id], $alternative_task->toArray()));
        $response->assertCreated()->
            assertJsonStructure(['data' => ['task' => ['user' => ['id', 'name'], 'id', 'title', 'description', 'status']]])->
            assertJsonFragment(['id' => $task->id, 'title' => $alternative_task->title, 'description' => $alternative_task->description]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => $alternative_task->title, 'description' => $alternative_task->description]);
    }

    public function testAuthenticatedUserCanNotEditInformationOfOtherUserTask()
    {
        $user_1 = factory(User::class)->create();
        $user_2 = factory(User::class)->create();
        $token_1 = $user_1->createToken('test-token');
        $task_2 = $user_2->tasks()->create(factory(Task::class)->make()->toArray());
        $alternative_task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token_1->plainTextToken])->
            postJson($this->api_edit, array_merge(['task_id' => $task_2->id], $alternative_task->toArray()));
        $response->assertStatus(HttpStatus::BadRequest)->
            assertExactJson(['errors' => ['task_id' => ['The selected task id is invalid.']]]);
        $this->assertDatabaseHas('tasks', array_merge(['user_id' => $user_2->id], $task_2->toArray()));
    }

    public function testAuthenticatedUserCanNotEditInformationUsingInvalidInputData()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_edit, ['task_id' => $task->id, 'title' => '', 'description' => null]);
        $response->assertStatus(HttpStatus::BadRequest)->
            assertJsonFragment(['title' => ["The title field is required."], 'description' => ["The description field is required."]]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id, 'user_id' => $user->id, 'title' => '', 'description' => null]);
    }

}
