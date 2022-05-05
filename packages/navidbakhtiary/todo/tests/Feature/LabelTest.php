<?php

namespace NavidBakhtiary\ToDo\Tests\Feature;

use App\Models\User;
use NavidBakhtiary\ToDo\Models\Label;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NavidBakhtiary\ToDo\Config\HttpStatus;
use Tests\TestCase;

class LabelTest extends TestCase
{
    use RefreshDatabase;

    private $api_prefix = '/todo/labels/';
    private $api_add = '/todo/labels/add';
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

    public function testUseInvalidInputForCreateLabelByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['name' => 1]);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['name' => ["The name must be a string."]]);
        $this->assertDatabaseMissing('labels', ['name' => '1']);
    }

    public function testCreateLabelByUnauthenticatedUser()
    {
        $label = factory(Label::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . hash('sha256', 'fake token')])->
            postJson($this->api_add, ['name' => $label->name]);
        $response->assertUnauthorized();
        $this->assertDatabaseMissing('labels', ['name' => $label->name]);
    }

    public function testCreateExistedLabelByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $label = factory(Label::class)->create();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['name' => $label->name]);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['name' => ["The name has already been taken."]]);
        $this->assertTrue(Label::where('name', $label->name)->count() == 1);
    }
}
