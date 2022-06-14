<?php

namespace NavidBakhtiary\ToDo\Tests\Feature;

use App\User as AppUser;
use NavidBakhtiary\ToDo\Models\User;
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
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label = factory(Label::class)->create();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['task_id' => $task->id, 'label_id' => $label->id]);
        $response->assertCreated()->assertJsonStructure(['data' => ['task label' => ['task' => ['user' => ['id', 'name'], 'title', 'description', 'status'], 'new label' => ['id', 'name']]]]);
        $this->assertDatabaseHas('task_label', ['task_id' => $task->id, 'label_id' => $label->id]);
    }

    public function testAuthenticatedUserCanNotAddLabelToOtherUserTask()
    {
        $app_user = factory(AppUser::class)->create();
        $token_1 = $app_user->createToken('test-token');
        $user_1 = new User($app_user);
        $user_2 = new User(factory(AppUser::class)->create());
        $task_2 = $user_2->tasks()->create(factory(Task::class)->make()->toArray());
        $label = factory(Label::class)->create();
        $existed_records_count = $task_2->labels()->count();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token_1->plainTextToken])->
            postJson($this->api_add, ['task_id' => $task_2->id, 'label_id' => $label->id]);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['errors' => ['task_id' => ['The selected task id is invalid.']]]);
        $this->assertTrue($existed_records_count == $task_2->labels()->count());
    }

    public function testUnauthenticatedUserCanNotAddLabelToTask()
    {
        $user = new User(factory(AppUser::class)->create());
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label = factory(Label::class)->create();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . hash('sha256', 'fake token')])->
            postJson($this->api_add, ['task_id' => $task->id, 'label_id' => $label->id]);
        $existed_records_count = $task->labels()->count();
        $response->assertUnauthorized();
        $this->assertTrue($existed_records_count == $task->labels()->count());
    }

    public function testAuthenticatedUserCanNotAddAttachedLabelToTaskAgain()
    {
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label = factory(Label::class)->create();
        $task->labels()->attach($label->id);
        $existed_records_count = $task->labels()->count();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['task_id' => $task->id, 'label_id' => $label->id]);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['errors' => ['label_id' => ['The label has already been attached.']]]);
        $this->assertTrue($existed_records_count == $task->labels()->count());
    }
}
