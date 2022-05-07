<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVendorCheckoutMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $check_shipping;
    public $vendor_details;
    public $exist_default;
    public $vendor_total;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $check_shipping, $vendor_details, $exist_default, $vendor_total)
    {
        $this->data           = $data;
        $this->check_shipping = $check_shipping;
        $this->vendor_details        = $vendor_details;
        $this->exist_default  = $exist_default;
        $this->vendor_total  = $vendor_total;
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
            ->markdown('checkout.vendor_checkout')->with([
           // 'token' => $this->token,
            'data'           => $this->data,
            'check_shipping' => $this->check_shipping,
            'vendor_details' => $this->vendor_details,
            'exist_default'  => $this->exist_default,
            'vendor_total'   => $this->vendor_total,
        ]);
    }
}
