<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAdminTicketRequestMail extends Mailable
{
    use Queueable, SerializesModels;
    public $ticket_no;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticket_no)
    {
        $this->ticket_no = $ticket_no;
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
            ->markdown('TicketRequest.ticket_request')->with([
           // 'token' => $this->token,
            'ticket_no' => $this->ticket_no,
        ]);
    }
}
