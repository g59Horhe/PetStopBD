<?php
// Email functions for PetStopBD

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send OTP via email
function send_otp_email($email, $otp) {
    // Email subject
    $subject = "Your PetStopBD Verification Code";
    
    // Email body
    $message = "
    <html>
    <head>
        <title>PetStopBD - Email Verification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #1E88E5; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; }
            .otp-box { font-size: 24px; font-weight: bold; text-align: center; padding: 10px; background-color: #f5f5f5; border-radius: 5px; margin: 20px 0; letter-spacing: 5px; }
            .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>PetStopBD - Email Verification</h2>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>Thank you for registering with PetStopBD. To complete your registration, please use the verification code below:</p>
                <div class='otp-box'>$otp</div>
                <p>This code will expire in 15 minutes.</p>
                <p>If you did not request this verification code, please ignore this email.</p>
                <p>Regards,<br>The PetStopBD Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " PetStopBD. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // For development/testing purposes, save the email to a file
        $file_path = dirname(__DIR__) . '/logs/emails/';
        if (!file_exists($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $file = fopen($file_path . 'email_to_' . str_replace('@', '_at_', $email) . '_' . time() . '.html', 'w');
        fwrite($file, $message);
        fclose($file);

    
    // Use PHPMailer to send the email
    return send_email_phpmailer($email, $subject, $message);
}

// Function to send password reset email
function send_password_reset_email($email, $token) {
    // Email subject
    $subject = "PetStopBD - Password Reset Code";
    
    // Format token for display (can add hyphens for readability)
    $formatted_token = wordwrap($token, 8, '-', true);
    
    // Email body
    $message = "
    <html>
    <head>
        <title>PetStopBD - Password Reset Code</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #1E88E5; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; }
            .code-box { 
                font-family: monospace; 
                font-size: 18px; 
                font-weight: bold; 
                text-align: center; 
                padding: 15px; 
                background-color: #f5f5f5; 
                border-radius: 5px; 
                margin: 20px 0; 
                border: 1px dashed #ccc;
                letter-spacing: 2px;
            }
            .instructions { background-color: #ffffd9; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>PetStopBD - Password Reset Code</h2>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>We received a request to reset your password. Please use the code below to reset your password:</p>
                
                <div class='code-box'>$formatted_token</div>
                
                <div class='instructions'>
                    <p><strong>How to reset your password:</strong></p>
                    <ol>
                        <li>Go to <a href='http://" . $_SERVER['HTTP_HOST'] . "/petstopbd/auth/reset_password.php'>Password Reset Page</a></li>
                        <li>Copy and paste the code above when prompted</li>
                        <li>Create your new password</li>
                    </ol>
                </div>
                
                <p>This code will expire in 1 hour for security reasons.</p>
                <p>If you did not request a password reset, please ignore this email and your account will remain secure.</p>
                <p>Regards,<br>The PetStopBD Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " PetStopBD. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // For development/testing purposes, save the email to a file
        $file_path = dirname(__DIR__) . '/logs/emails/';
        if (!file_exists($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $file = fopen($file_path . 'reset_email_to_' . str_replace('@', '_at_', $email) . '_' . time() . '.html', 'w');
        fwrite($file, $message);
        fclose($file);
    

    // Use PHPMailer to send the email
    return send_email_phpmailer($email, $subject, $message);
}
// Use PHPMailer for reliable email sending
function send_email_phpmailer($to, $subject, $message) {
    // Check if we should load using Composer autoloader
    $composer_autoload = dirname(__DIR__) . '/vendor/autoload.php';
    
    if (file_exists($composer_autoload)) {
        require_once $composer_autoload;
    } else {
        // If Composer is not used, provide a fallback
        // For the fallback, just use PHP's mail() function
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: PetStopBD <noreply@petstopbd.com>\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('your-email@gmail.com', 'PetStopBD');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        
        // For development, output errors directly
            echo "<div class='alert alert-warning'>Email Error: " . $mail->ErrorInfo . "</div>";
        
        
        // Try fallback to regular mail() function
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: PetStopBD <noreply@petstopbd.com>\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
}
?>
