<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


require_once("../vendor/autoload.php");

function checkActive($pageData=[], $page) {
    if ($pageData['active_page'] === $page) {
        return "active";
    }
    return "";
}

function createToken() {
    $seed = random_bytes(8);
    $t = time();
    $hash = hash_hmac('sha256', session_id() . $seed . $t, CSRF_TOKEN_SECRET, true);
    return urlSafeEncode($hash . '|' . $seed . '|' . $t);
}

function validateToken($token) {
    $parts = explode('|',urlSafeDecode($token));
    if (count($parts) === 3) {
        $hash = hash_hmac('sha256', session_id() . $parts[1]  . $parts[2], CSRF_TOKEN_SECRET, true);
        if (hash_equals($hash, $parts[0])) {
            return true;
        }
    } else if (count($parts) > 3) {
        $length = count($parts);
        $hash = hash_hmac('sha256', session_id() . $parts[$length -2] . $parts[$length - 1], CSRF_TOKEN_SECRET, true);
        $random_bytes = "";
        for ($i = 0; $i < $length-2; $i++) {
            if ($i === $length - 3) {
                $random_bytes = $random_bytes . $parts[$i];
            } else {
                $random_bytes = $random_bytes . $parts[$i] . "|";
            }
        }
        if (hash_equals($hash, $random_bytes)) {
            return true;
        }
    }
    return false;
}

function urlSafeEncode($m) {
    return rtrim(strtr(base64_encode($m), '+/', '-_'), '=');
}

function urlSafeDecode($m) {
 return base64_decode(strtr($m, '-_', '+/'));
}

function sendMail($subject, $msg, $to) {
    $mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;

    $mail->setFrom(EMAIL_FROM, EMAIL_NAME);
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $msg;

    $mail->send();

} catch(Exception $e) {
    echo $e->getMessage();
}

}
