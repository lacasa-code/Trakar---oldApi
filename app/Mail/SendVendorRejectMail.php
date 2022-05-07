<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVendorRejectMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $reason;
    public $rej_fields;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $reason, $rej_fields)
    {
        $this->name = $name;
        $this->reason   = $reason;
        $this->rej_fields   = $rej_fields;
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
            ->markdown('VendorRequest.reject')->with([
           // 'token' => $this->token,
            'name' => $this->name,
            'reason'   => $this->reason,
            'rej_fields'   => $this->rej_fields,
        ]);
    }
}
