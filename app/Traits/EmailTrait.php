<?php 

namespace App\Traits;

use App\Mail\MailNotify;
use Illuminate\Support\Facades\Mail;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

trait EmailTrait{
    public function sendEmailTrait($email, $subject,  $body, $user_name){
       /*
        $data = [
            'to' => $email,
            'user_name' => $user_name,
            'subject' => $subject,
            'body' => $body,
            'from' => env('MAIL_FROM_ADDRESS', 'support@safiribytes.com'),
            'from_name' => env('MAIL_FROM_NAME','Style Yangu'),
            'reply_to' => env('MAIL_FROM_ADDRESS', 'support@safiribytes.com'),
            'reply_to_name' => env('MAIL_FROM_NAME','Style Yangu'),
        ];

       $response = Mail::to($data['to'])->send(new MailNotify($data));
        return $response;
        */

    }

    public function sendEmailTrait1($email, $subject,  $body, $user_name)
    {

        $receiverEmail = $email;
        $bccEmails = ['martinwainaina001@gmail.com'];
        $mailDns = "";
        $fromMail = 'support@safiribytes.com';

        $transport = Transport::fromDsn($mailDns);
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from($fromMail)
            ->to($receiverEmail)
            // ->addTo(...$bccEmails)
            // ->bcc(...$bccEmails)
            ->subject($subject)
            // ->text($body)
            // ->html($body); /// send only body without html tags
            ->html("<!DOCTYPE html>
            <html>
            <head>
                <title>$subject</title>
            </head>
            <body>
                <h2>Dear $user_name, </h2> </b>
            
                <p>$body</p>  </b>
            
                <p>Kind Regards, </p>
                <p>Safiri Tours</p>
            </body>
            </html>");


        return $mailer->send($email);
    }
}

