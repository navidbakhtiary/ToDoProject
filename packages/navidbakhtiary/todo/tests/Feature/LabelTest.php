<?php

namespace NavidBakhtiary\ToDo\Tests\Feature;

use App\Models\User;
use NavidBakhtiary\ToDo\Models\Label;
use NavidBakhtiary\ToDo\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NavidBakhtiary\ToDo\Config\HttpStatus;
use Tests\TestCase;

class LabelTest extends TestCase
{
    use RefreshDatabase;

    private $api_add = '/todo/label/add';
    private $api_list = '/todo/label';
    private $bearer_prefix = 'Bearer ';

    public function testCreateLabelByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $label = factory(Label::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['name' => $label->name]);
        $response->assertCreated()->assertJsonFragment(['name' => $label->name]);
        $this->assertDatabaseHas('labels', ['name' => $label->name]);
    }

    public function testAuthenticatedUserCanNotCreateLabelUsingInvalidInputData()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['name' => 1]);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['name' => ["The name must be a string."]]);
        $this->assertDatabaseMissing('labels', ['name' => '1']);
    }

    public function testUnauthenticatedUserCanNotCreateLabel()
    {
        $label = factory(Label::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . hash('sha256', 'fake token')])->
            postJson($this->api_add, ['name' => $label->name]);
        $response->assertUnauthorized();
        $this->assertDatabaseMissing('labels', ['name' => $label->name]);
    }

    public function testAuthenticatedUserCanNotCreateLabelUsingExistedName()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $label = factory(Label::class)->create();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['name' => $label->name]);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['name' => ["The name has already been taken."]]);
        $this->assertTrue(Label::where('name', $label->name)->count() == 1);
    }

    public function testAuthenticatedUserCanGetListOfLabels()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $task_1 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $task_2 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label_1 = factory(Label::class)->create();
        $label_2 = factory(Label::class)->create();
        $task_1->labels()->attach($label_1->id);
        $task_2->labels()->sync([$label_1->id, $label_2->id]);
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->getJson($this->api_list);
        $response->assertOk()->assertJson(['data' => ['labels' => [['id' => $label_1->id, 'name' => $label_1->name, 'tasks count' => 2], ['id' => $label_2->id, 'name' => $label_2->name, 'tasks count' => 1]]]]);
    }

}
