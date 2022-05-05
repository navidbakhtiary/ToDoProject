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
    private $bearer_prefix = 'Bearer ';

    public function testCreateLabelByAuthenticatedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test-token');
        $label = factory(Label::class)->make();
        $response = $this->
            withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_prefix . 'add', ['name' => $label->name]);
        $response->assertCreated()->assertJsonFragment(['name' => $label->name]);
        $this->assertDatabaseHas('labels', ['name' => $label->name]);
    }
}
