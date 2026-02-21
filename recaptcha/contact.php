<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// require ReCaptcha class
require('recaptcha-master/src/autoload.php');

// configure
// an email address that will be in the From field of the email.
$from = 'Demo contact form <contact@rsgaugeworks.com>';

// an email address that will receive the email with the output of the form
$sendTo = 'Demo contact form <tipnmog@gmail.com>';

// subject of the email
$subject = 'New message from contact form';

// form field names and their translations.
// array variable name => Text to appear in the email
$fields = array('name' => 'Name', 'surname' => 'Last Name', 'phone' => 'Phone', 'email' => 'Email', 'message' => 'Message');

// message that will be displayed when everything is OK :)
$okMessage = 'Contact form successfully submitted. Thank you, I will get back to you soon!';

// If something goes wrong, we will display this message.
$errorMessage = 'There was an error while submitting the form. Please try again later';



// Instantiation and passing `true` enables exceptions
$mail = new PHPMailer(true);

// ReCaptch Secret
$recaptchaSecret = '6Lcm9uYUAAAAAGJerTtxPC5fD4u5km_3eJQY1i_c';

// let's do the sending

// if you are not debugging and don't need error reporting, turn this off by error_reporting(0);
error_reporting(E_ALL & ~E_NOTICE);

try {
    if (!empty($_POST)) {

        // validate the ReCaptcha, if something is wrong, we throw an Exception,
        // i.e. code stops executing and goes to catch() block
        
        if (!isset($_POST['g-recaptcha-response'])) {
            throw new \Exception('ReCaptcha is not set.');
        }

        // do not forget to enter your secret key from https://www.google.com/recaptcha/admin
        
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret, new \ReCaptcha\RequestMethod\CurlPost());
        
        // we validate the ReCaptcha field together with the user's IP address
        
        $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if (!$response->isSuccess()) {
            throw new \Exception('ReCaptcha was not validated.');
        }
        
        // everything went well, we can compose the message, as usually
        
        $emailTextHtml = "<p>You have a new message from your contact form</p><hr>";
        $emailTextHtml .= "<table>";

        foreach ($_POST as $key => $value) {
            // If the field exists in the $fields array, include it in the email
            if (isset($fields[$key])) {
                $emailTextHtml .= "<tr><th>$fields[$key]</th><td>$value</td></tr>";
            }
        }
        $emailTextHtml .= "</table><hr>";
        $emailTextHtml .= "<p>Have a nice day,<br>Best,<br>Cool Web Page Developer</p>";
    
        
        // Send email
        $mail->SMTPDebug = 0;                                               // Enable verbose debug output  SMTP::DEBUG_SERVER.  0 = none
        $mail->isSMTP();                                                    // Send using SMTP
        $mail->Host       = 'p3plcpnl0518.prod.phx3.secureserver.net ';     // Set the SMTP server to send through
        $mail->SMTPAuth   = false;                                          // no SMTP authentication for GoDaddy
        $mail->SMTPSecure = 'none';                                         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged - none for GoDaddy
        $mail->Port       = 587;                                            // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above.  Some GoDaddy info suggests 25
    
        //Recipients
        $mail->setFrom('contact@rsgaugeworks.com', 'RSGaugeworks');
        $mail->addAddress('rsgaugeworks@msn.com', 'Randy Stebbins');           // Add a recipient
        $mail->addReplyTo('contact@rsgaugeworks.com', 'RSGaugeworks');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');
    
        // Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'RSGaugeworks Contact Form Submission';
        $mail->Body    = $emailTextHtml;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    
        $mail->send();

        $responseArray = array('type' => 'success', 'message' => $okMessage);
    }
} catch (\Exception $e) {
    $responseArray = array('type' => 'danger', 'message' => $e->getMessage());
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
} else {
    echo $responseArray['message'];
}
