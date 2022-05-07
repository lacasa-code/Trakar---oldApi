<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCheckoutVendorToAdminMail extends Mailable
{
    use Queueable, SerializesModels;
    public $admin_data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($admin_data)
    {
        $this->admin_data = $admin_data;
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
            ->markdown('checkout.checkout_to_admin')->with([
           // 'token' => $this->token,
            'admin_data' => $this->admin_data
        ]);
    }
}
