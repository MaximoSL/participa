<?php

namespace MXAbierto\Participa\Listeners;

use Illuminate\Contracts\Mail\MailQueue;
use MXAbierto\Participa\Events\UserHasRegisteredEvent;

/**
 * The send email confirmation listener.
 */
class SendEmailConfirmationListener
{
    /**
     * Creates a new send email confirmation listener instance.
     *
     * @param \Illuminate\Contracts\Mail\MailQueue $mailer
     *
     * @return void
     */
    public function __construct(MailQueue $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handles the user has registered event.
     *
     * @param \MXAbierto\Participa\Events\UserHasRegisteredEvent $event;
     *
     * @return void
     */
    public function handler(UserHasRegisteredEvent $event)
    {
        $mail = [
            'email' => $event->user->email,
            'token' => $event->user->token,
        ];

        //Send email to user for email account verification
        $this->mailer->queue('email.signup', ['token' => $token], function ($message) use ($mail) {
            $message->subject(trans('messages.confirmationtitle'));
            $message->from(trans('messages.emailfrom'), trans('messages.emailfromname'));
            $message->to($mail['email']); // Recipient address
        });
    }
}
