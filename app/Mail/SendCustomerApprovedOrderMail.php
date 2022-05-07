<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCustomerApprovedOrderMail extends Mailable
{
    use Queueable, SerializesModels;
    public $customer_name;
    public $vendor_name;
    public $order_number;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer_name, $vendor_name, $order_number)
    {
        $this->customer_name = $customer_name;
        $this->vendor_name   = $vendor_name;
        $this->order_number  = $order_number;
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
            ->markdown('VendorRequest.approved_order')->with([
            'customer_name'  => $this->customer_name,
            'vendor_name'    => $this->vendor_name,
            'order_number'   => $this->order_number,
        ]);
    }
}
