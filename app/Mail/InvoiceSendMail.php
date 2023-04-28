<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class InvoiceSendMail extends Mailable {

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() { 
       
        return $this->view('emails.invoiceAttached')
        ->attach(public_path('invoices')."/".$this->data['fileName'])
        ->from($this->data['fromEmail'],$this->data['fromName'])
        ->subject($this->data['subject'])
        ->with(['email_content'=>$this->data['emailBody']]);
    }
    
}
