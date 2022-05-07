<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendStaffRegisterEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $role;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $role)
    {
        $this->name = $name;
        $this->role   = $role;
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
            ->markdown('VendorRequest.invite_staff')->with([
           // 'token' => $this->token,
            'name'   => $this->name,
            'role'   => $this->role,
            // 'code' => $this->code
        ]);
    }
}
