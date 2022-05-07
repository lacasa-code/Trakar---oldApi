<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCheckoutMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $check_shipping;
    public $details;
    public $exist_default;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $check_shipping, $details, $exist_default)
    {
        $this->data           = $data;
        $this->check_shipping = $check_shipping;
        $this->details        = $details;
        $this->exist_default  = $exist_default;
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
            ->markdown('checkout.checkout')->with([
           // 'token' => $this->token,
            'data'           => $this->data,
            'check_shipping' => $this->check_shipping,
            'details'        => $this->details,
            'exist_default'  => $this->exist_default,
        ]);
    }
}
