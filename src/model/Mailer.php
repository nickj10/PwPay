<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public function sendEmail($id, $to) {

        $mail = new PHPMailer(true);
        try {
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER; 
            $mail->isSMTP();
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'TLS';
            $mail->Username = $_ENV['SENDGRID_USERNAME'];
            $mail->Password = $_ENV['SENDGRID_PASSWORD'];
            $mail->Port = 587;
            $mail->isHTML(true);
            $mail->setFrom('kayeann.ignacio@students.salle.url.edu', 'Pwpay');
            $mail->addAddress('kayeann.sn@gmail.com');
            $mail->Subject = 'Activation Link';
            $mail->Body = 'Welcome to Pwpay! Click the following link to activate your account: http://' . $_SERVER['HTTP_HOST'] . '/activate-token=' . $id;
            $mail->send();
            return true;
        } catch (Exception $e) {
            //$e->getMessage() . $mail->ErrorInfo;
            return false;
        }
    }

}