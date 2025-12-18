<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer/PHPMailer.php';

function sendOrderConfirmation($userEmail, $orderId) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                            
        $mail->Host       = 'dvlaicu.daw.ssmr.ro';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'supermarket@dvlaicu.daw.ssmr.ro';
        $mail->Password   = 'supermarket';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('supermarket@dvlaicu.daw.ssmr.ro', 'Supermarket');
        $mail->addAddress($userEmail);

        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation: #$orderId";
        
        $mail->Body    = "
            <h1>Thanks for the order!</h1>
            <p>Your order <b>#$orderId</b> has been recieved and we will process it shortly.</p>
        ";
        
        $mail->AltBody = "Multumim pentru comanda! Id-ul este #$orderId. Comanda dvs. va fi procesata in cel mai scurt timp.";

        $mail->send();
        return true;

    } catch (Exception $_) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendRegisterConfirmation($userEmail) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                            
        $mail->Host       = 'dvlaicu.daw.ssmr.ro';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'supermarket@dvlaicu.daw.ssmr.ro';
        $mail->Password   = 'supermarket';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('supermarket@dvlaicu.daw.ssmr.ro', 'Supermarket');
        $mail->addAddress($userEmail);

        $mail->isHTML(true);
        $mail->Subject = "Welcome!";
        
        $mail->Body    = "
            <h1>Thanks for joining us!</h1>
            <p>We are trying our best to deliver the best experience for our customers!</p>
        ";
        
        $mail->AltBody = "Welcome! We are trying our best to deliver the best experience for our customers!";

        $mail->send();
        return true;

    } catch (Exception $_) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>