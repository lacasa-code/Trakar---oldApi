<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SendCheckoutMail;
use App\Mail\SendVendorCheckoutMail;
use Mail;

class SendCheckoutEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $send_mail;
    public $data;
    public $check_shipping;
    public $vendor_details;
    public $exist_default;
    public $vendor_total;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($send_mail, $data, $check_shipping, $vendor_details, $exist_default, $vendor_total)
    {
        $this->send_mail = $send_mail;
        $this->data = $data;
        $this->check_shipping = $check_shipping;
        $this->vendor_details        = $vendor_details;
        $this->exist_default  = $exist_default;
        $this->vendor_total  = $vendor_total;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new SendVendorCheckoutMail($this->data, $this->check_shipping, $this->vendor_details, $this->exist_default, $this->vendor_total);        
        Mail::to($this->send_mail)->send($email);
    }
}
