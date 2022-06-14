<?php

namespace NavidBakhtiary\ToDo\Tests\Feature;

use App\User as AppUser;
use NavidBakhtiary\ToDo\Models\User;
use NavidBakhtiary\ToDo\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use NavidBakhtiary\ToDo\Config\HttpStatus;
use NavidBakhtiary\ToDo\Models\Label;
use NavidBakhtiary\ToDo\Notifications\TaskStatusClosed;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private $api_add = '/todo/task/add';
    private $api_edit = '/todo/task/edit';
    private $api_list = '/todo/task';
    private $api_details = '/todo/task/';
    private $api_status_switching = '/todo/task/status/switch';
    private $bearer_prefix = 'Bearer ';

    public function testCreateTaskByAuthenticatedUser()
    {
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, $task->toArray());
        $response->assertCreated()->assertJsonStructure(['data' => ['task' => ['user' => ['id', 'name'], 'title', 'description', 'status']]]);
        $this->assertDatabaseHas('tasks', array_merge(['user_id' => $user->id], $task->toArray()));
    }

    public function testAuthenticatedUserCanNotCreateTaskUsingInvalidInputData()
    {
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_add, ['description' => $task->description, 'status' => 'Unknown']);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['title' => ["The title field is required."], 'status' => ['The selected status is invalid.']]);
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
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
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
        $app_user = factory(AppUser::class)->create();
        $token_1 = $app_user->createToken('test-token');
        $user_1 = new User($app_user);
        $user_2 = new User(factory(AppUser::class)->create());
        $task_2 = $user_2->tasks()->create(factory(Task::class)->make()->toArray());
        $alternative_task = factory(Task::class)->make();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token_1->plainTextToken])->postJson($this->api_edit, array_merge(['task_id' => $task_2->id], $alternative_task->toArray()));
        $response->assertStatus(HttpStatus::BadRequest)->assertExactJson(['errors' => ['task_id' => ['The selected task id is invalid.']]]);
        $this->assertDatabaseHas('tasks', array_merge(['user_id' => $user_2->id], $task_2->toArray()));
    }

    public function testAuthenticatedUserCanNotEditInformationUsingInvalidInputData()
    {
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->postJson($this->api_edit, ['task_id' => $task->id, 'title' => '', 'description' => null]);
        $response->assertStatus(HttpStatus::BadRequest)->assertJsonFragment(['title' => ["The title field is required."], 'description' => ["The description field is required."]]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id, 'user_id' => $user->id, 'title' => '', 'description' => null]);
    }

    public function testAuthenticatedUserCanChangeStatusOfOwnTask()
    {
        $app_user = factory(AppUser::class)->make();
        $app_user->email = 'navidbakhtiary@yahoo.com';
        $app_user->save();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray()); //status is Open
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_status_switching, ['task_id' => $task->id]);
        $response->assertCreated()->assertJsonFragment(['user' => ['id' => $user->id, 'name' => $user->name], 'id' => $task->id, 'status' => Task::$status_close]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => Task::$status_close]);
        //To check that an email is sent, you must check the mailtrap.io inbox
        $this->assertDatabaseHas('notifications', ['type' => TaskStatusClosed::class, 'notifiable_type' => Task::class, 'notifiable_id' => $task->id]);
    }

    public function testAuthenticatedUserCanNotChangeStatusOfOtherUserTask()
    {
        $app_user = factory(AppUser::class)->create();
        $token_1 = $app_user->createToken('test-token');
        $user_1 = new User($app_user);
        $user_2 = new User(factory(AppUser::class)->create());
        $task_2 = $user_2->tasks()->create(factory(Task::class)->make()->toArray()); //status is Open
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token_1->plainTextToken])->postJson($this->api_status_switching, ['task_id' => $task_2->id]);
        $response->assertStatus(HttpStatus::BadRequest)->assertExactJson(['errors' => ['task_id' => ['The selected task id is invalid.']]]);
        $this->assertDatabaseHas('tasks', ['id' => $task_2->id, 'status' => 'Open']);
    }

    public function testAuthenticatedUserCanGetListOfTasks()
    {
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task_1 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $task_2 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label_1 = factory(Label::class)->create();
        $label_2 = factory(Label::class)->create();
        $task_1->labels()->attach($label_1->id);
        $task_2->labels()->sync([$label_1->id, $label_2->id]);
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->getJson($this->api_list);
        $response->assertOk()->assertExactJson(
            [
                'data' =>
                [
                    'tasks' =>
                    [
                        [
                            'id' => $task_1->id,
                            'title' => $task_1->title,
                            'description' => $task_1->description,
                            'labels' =>
                            [
                                ['id' => $label_1->id, 'name' => $label_1->name, 'tasks count' => 2]
                            ]
                        ],
                        [
                            'id' => $task_2->id,
                            'title' => $task_2->title,
                            'description' => $task_2->description,
                            'labels' =>
                            [
                                ['id' => $label_1->id, 'name' => $label_1->name, 'tasks count' => 2],
                                ['id' => $label_2->id, 'name' => $label_2->name, 'tasks count' => 1]
                            ]
                        ],
                    ]
                ]
            ]
        );
    }

    public function testLabelsSubListInAuthenticatedUserTasksListNotIncludeNumberOfOtherUsersTasks()
    {
        $app_user = factory(AppUser::class)->create();
        $token_a = $app_user->createToken('test-token');
        $user_a = new User($app_user);
        $user_b = new User(factory(AppUser::class)->create());
        $label_1 = factory(Label::class)->create();
        $label_2 = factory(Label::class)->create();
        $label_3 = factory(Label::class)->create();
        $task_a1 = $user_a->tasks()->create(factory(Task::class)->make()->toArray());
        $task_a2 = $user_a->tasks()->create(factory(Task::class)->make()->toArray());
        $task_b1 = $user_b->tasks()->create(factory(Task::class)->make()->toArray());
        $task_a1->labels()->sync([$label_1->id, $label_2->id]);
        $task_a2->labels()->sync([$label_1->id, $label_3->id]);
        $task_b1->labels()->sync([$label_1->id, $label_2->id, $label_3->id]);
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token_a->plainTextToken])->getJson($this->api_list);
        $response->assertOk()->assertExactJson(
            [
                'data' =>
                [
                    'tasks' =>
                    [
                        [
                            'id' => $task_a1->id,
                            'title' => $task_a1->title,
                            'description' => $task_a1->description,
                            'labels' =>
                            [
                                ['id' => $label_1->id, 'name' => $label_1->name, 'tasks count' => 2],
                                ['id' => $label_2->id, 'name' => $label_2->name, 'tasks count' => 1],
                            ]
                        ],
                        [
                            'id' => $task_a2->id,
                            'title' => $task_a2->title,
                            'description' => $task_a2->description,
                            'labels' =>
                            [
                                ['id' => $label_1->id, 'name' => $label_1->name, 'tasks count' => 2],
                                ['id' => $label_3->id, 'name' => $label_3->name, 'tasks count' => 1]
                            ]
                        ],
                    ]
                ]
            ]
        );
    }

    public function testUnauthenticatedUserCanNotGetTasksList()
    {
        $user = new User(factory(AppUser::class)->create());
        $task_1 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $task_2 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label_1 = factory(Label::class)->create();
        $label_2 = factory(Label::class)->create();
        $task_1->labels()->attach($label_1->id);
        $task_2->labels()->sync([$label_1->id, $label_2->id]);
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . hash('sha256', 'fake token')])->getJson($this->api_list);
        $response->assertUnauthorized()->assertJsonMissing(['data' => ['tasks' => []]]);
    }

    public function testAuthenticatedUserCanGetDetailsOfOwnTask()
    {
        $app_user = factory(AppUser::class)->create();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task_1 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $task_2 = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $label_1 = factory(Label::class)->create();
        $label_2 = factory(Label::class)->create();
        $task_1->labels()->attach($label_1->id);
        $task_2->labels()->sync([$label_1->id, $label_2->id]);
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            getJson($this->api_details . $task_2->id);
        $response->assertOk()->assertExactJson([
            'data' => 
            [
                'task' => 
                [
                    'id' => $task_2->id,
                    'title' => $task_2->title,
                    'description' => $task_2->description,
                    'status' => $task_2->status,
                    'created at' => $task_2->created_at,
                    'last updated at' => $task_2->updated_at,
                    'labels' =>
                    [
                        ['id' => $label_1->id, 'name' => $label_1->name, 'tasks count' => 2],
                        ['id' => $label_2->id, 'name' => $label_2->name, 'tasks count' => 1]
                    ]

                ]
            ]
        ]);
    }

    public function testAuthenticatedUserCanNotGetDetailsOfOtherUserTask()
    {
        $app_user = factory(AppUser::class)->create();
        $token_1 = $app_user->createToken('test-token');
        $user_1 = new User($app_user);
        $user_2 = new User(factory(AppUser::class)->create());
        $task_2 = $user_2->tasks()->create(factory(Task::class)->make()->toArray());
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token_1->plainTextToken])->
            getJson($this->api_details . $task_2->id);
        $response->assertStatus(HttpStatus::BadRequest)->assertExactJson(['errors' => ['task_id' => ['The selected task id is invalid.']]]);
    }

    public function testUnauthenticatedUserCanNotGetDetailsOfTask()
    {
        $user = new User(factory(AppUser::class)->create());
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray());
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . hash('sha256', 'fake token')])->
            getJson($this->api_details . $task->id);
        $response->assertUnauthorized()->assertJsonMissing(['data' => ['task' => []]]);
    }

    public function testSendEmailNotificationWhenUserChangeStatusOfOwnTask()
    {
        Notification::fake();

        $app_user = factory(AppUser::class)->create();
        $user = new User($app_user);
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray()); //status is Open
        $task->status = Task::$status_close;
        $task->save();

        Notification::assertNothingSent();

        $task->notify(new TaskStatusClosed());
        
        Notification::assertSentTo(
            $task,
            TaskStatusClosed::class,
            function ($notification) use ($user, $task) {
                $mail_data = $notification->toMail($task)->introLines;
                $db_data = $notification->toArray($task);
                $this->assertContains('One of your tasks has been closed.', $mail_data);
                $this->assertContains('Title of task: ' . $task->title, $mail_data);
                $this->assertContains($user->name, $db_data['task notification']['task owner']);
                return $db_data['task notification']['task']['id'] === $task->id;
            }
        );

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => Task::$status_close]);
    }

    public function testEmailWillNotSentWhenAuthenticatedUserChangeTaskStatusToOpen()
    {
        $app_user = factory(AppUser::class)->make();
        $app_user->email = 'navidbakhtiary@yahoo.com';
        $app_user->save();
        $token = $app_user->createToken('test-token');
        $user = new User($app_user);
        $task = $user->tasks()->create(factory(Task::class)->make()->toArray()); //status is Open
        $task->status = Task::$status_close;
        $task->save();
        $response = $this->withHeaders(['Authorization' => $this->bearer_prefix . $token->plainTextToken])->
            postJson($this->api_status_switching, ['task_id' => $task->id]);
        $response->assertCreated()->assertJsonFragment(['user' => ['id' => $user->id, 'name' => $user->name], 'id' => $task->id, 'status' => Task::$status_open]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => Task::$status_open]);
        //To check that an email is not sent, you must check the mailtrap.io inbox
        $this->assertDatabaseCount('notifications', 0);
    }
}
