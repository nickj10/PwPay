<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public function sendEmail() {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
            $mail->isSMTP();
            //$mail->Host = 'mail.smtpbucket.com';
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'TLS';
            $mail->Username = 'apikey';
            $mail->Password = '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';
            $mail->Port = 587;
            $mail->isHTML(true);
            $mail->setFrom('kayeann.ignacio@students.salle.url.edu', 'Pwpay');
            $mail->addAddress('kayeann.sn@gmail.com');
            $mail->Subject = 'Activation Link';
            $mail->Body = 'Welcome to Pwpay! Click the following link to activate your account: ';
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo $e->getMessage() . $mail->ErrorInfo;
        }
    }

}