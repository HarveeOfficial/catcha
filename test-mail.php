<?php

require 'vendor/autoload.php';

$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->IsSMTP();
$mail->SMTPDebug = 2;
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;
$mail->Username = 'deseoharvey5@gmail.com';
$mail->Password = 'dpzlzhracgnqwbeg';
$mail->SetFrom('hello@example.com', 'Catcha');
$mail->AddAddress('deseoharvey5@gmail.com');
$mail->Subject = 'Test Email';
$mail->Body = 'This is a test email';

if (! $mail->Send()) {
    echo 'Mailer Error: '.$mail->ErrorInfo;
} else {
    echo 'Message sent successfully!';
}
