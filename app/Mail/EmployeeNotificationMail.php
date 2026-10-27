<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $subjectText;
    public $messageText;
    public $actionedBy;

    public function __construct($recipientName, $subjectText, $messageText, $actionedBy = null)
    {
        $this->recipientName = $recipientName;
        $this->subjectText   = $subjectText;
        $this->messageText   = $messageText;
        $this->actionedBy    = $actionedBy;
    }

    public function build()
    {
        return $this->subject($this->subjectText . ' - نظام الموارد البشرية')
            ->view('emails.employee_notification');
    }
}
