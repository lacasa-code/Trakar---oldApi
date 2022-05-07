<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendRegisterMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $id;
    // public $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $id)
    {
        $this->name = $name;
        $this->id   = $id;
       // $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       /// return $this->view('view.name');
        return $this
            ->markdown('Register.register')->with([
           // 'token' => $this->token,
            'name' => $this->name,
            'id'   => $this->id,
            // 'code' => $this->code
        ]);
    }
}
