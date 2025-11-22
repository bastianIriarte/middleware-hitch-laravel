<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminContactFormNotification extends Notification
{
    use Queueable;

    public $contactForm;
    public $userName;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($contactForm, $userName)
    {
        $this->contactForm = $contactForm;
        $this->userName = $userName;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
                    ->subject('Nuevo mensaje desde el formulario de contacto de tu sitio web ' . env('APP_NAME'))
                    ->greeting('Hola ' . $this->userName . '!, Tienes un nuevo mensaje desde el formulario de contacto:')
                    ->line('Nombre: ' . $this->contactForm->name)
                    ->line('Email: ' . $this->contactForm->email)
                    ->line('Teléfono: ' . $this->contactForm->mobile)
                    ->line('Modo de contacto preferido: ' . $this->contactForm->type_contact)
                    ->line('Asunto: ' . $this->contactForm->subject)
                    ->line('Mensaje: ' . $this->contactForm->message)
                    // ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!')
                    ->replyTo($this->contactForm->email);
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
            //
        ];
    }
}
