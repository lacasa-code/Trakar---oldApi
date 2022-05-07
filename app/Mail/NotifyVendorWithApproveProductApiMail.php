<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyVendorWithApproveProductApiMail extends Mailable
{
    use Queueable, SerializesModels;
    public $vendor_name;
    public $prod_name;
    public $prod_serial;
    public $prod_type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($vendor_name, $prod_name, $prod_serial, $prod_type)
    {
        $this->vendor_name = $vendor_name;
        $this->prod_name   = $prod_name;
        $this->prod_serial = $prod_serial;
        $this->prod_type   = $prod_type;
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
            ->markdown('VendorRequest.notify_vendor_product')->with([
           // 'token' => $this->token,
            'vendor_name' => $this->vendor_name,
            'prod_name'   => $this->prod_name,
            'prod_serial' => $this->prod_serial,
            'prod_type' => $this->prod_type
        ]);
    }
}
