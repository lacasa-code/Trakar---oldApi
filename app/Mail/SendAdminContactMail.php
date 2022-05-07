<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAdminContactMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $email;
    public $phone_number;
    public $message;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $phone_number, $message, $subject)
    {
        $this->name         = $name;
        $this->email        = $email;
        $this->phone_number = $phone_number;
        $this->message      = $message;
        $this->subject      = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //return $this->view('view.name');
       // $message->from($request->email);
        //$message->to('codingdriver15@gmail.com');
        return $this
            ->markdown('Contact.contact_us')->with([
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'message' => $this->message,
            'subject' => $this->subject,
        ]);
    }
}
