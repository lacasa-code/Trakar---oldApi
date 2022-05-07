<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVendorQuestionRequestMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user_name;
    public $product_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_name, $product_name)
    {
        $this->user_name = $user_name;
        $this->product_name   = $product_name;
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
            ->markdown('VendorRequest.send_vendor_question')->with([
           // 'token' => $this->token,
            'user_name' => $this->user_name,
            'product_name'   => $this->product_name,
            // 'code' => $this->code
        ]);
    }
}
