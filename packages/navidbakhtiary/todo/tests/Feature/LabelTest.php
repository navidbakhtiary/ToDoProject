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

    public function testCreateLabelByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $label = factory(Label::class)->make();
        $response = $this->
            withHeaders(['Authorization' => 'Bearer ' . $token->plainTextToken])->
            postJson($this->api_prefix . 'add', ['name' => $label->name]);
        $response->assertStatus(HttpStatus::Created);
        $this->assertDatabaseHas('labels', ['name' => $label->name]);
    }
}
