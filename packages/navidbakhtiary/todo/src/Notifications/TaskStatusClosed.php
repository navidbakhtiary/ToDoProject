<?php

namespace NavidBakhtiary\ToDo\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusClosed extends Notification implements ShouldQueue 
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('One of your tasks has been closed.')
                    ->line('Title of task: ' . $notifiable->title)
                    ->action('View Task Details', url('/todo/task/' . $notifiable->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'task notification' => [
                'task' => [
                    'id' => $notifiable->id,
                    'title' => $notifiable->title,
                ],
                'task owner' => [
                    'id' => $notifiable->user->id,
                    'name' => $notifiable->user->name,
                    'email' => $notifiable->user->email
                ],
                'message' => 'Task <'. $notifiable->title . '> has been closed.'
            ]
        ];
    }
}
