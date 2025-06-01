<?php
// OTP Verification page for PetStopBD

// Include necessary files
require_once '../config/db_connect.php';
require_once '../includes/auth_functions.php';
require_once '../includes/mail_functions.php';

// Start session
session_start();

// Check if user is already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit();
}

// Check if verification email exists in session
if (!isset($_SESSION['verification_email'])) {
    header('Location: register.php');
    exit();
}

$email = $_SESSION['verification_email'];
$otp = "";
$otp_err = "";
$verification_err = "";
$verification_success = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if resend OTP was requested
    if (isset($_POST['resend_otp'])) {
        // Generate new OTP
        $new_otp = generate_otp();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Update OTP in database
        $stmt = $conn->prepare("UPDATE user_verification SET otp = ?, otp_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $new_otp, $otp_expiry, $email);
        
        if ($stmt->execute()) {
            // Send OTP to email
            if (send_otp_email($email, $new_otp)) {
                $verification_success = "A new verification code has been sent to your email. Please check your inbox.";
            } else {
                $verification_err = "Failed to send verification email. Please try again.";
            }
        } else {
            $verification_err = "Failed to generate new verification code. Please try again.";
        }
    } else {
        // Validate OTP
        if (empty(trim($_POST["otp"]))) {
            $otp_err = "Please enter the verification code.";
        } else {
            $user_otp = trim($_POST["otp"]);
        }
        
        // Check input errors before verifying
        if (empty($otp_err)) {
            // Verify OTP
            $result = verify_otp($conn, $email, $user_otp);
            
            if ($result['success']) {
                // Clear verification email from session
                unset($_SESSION['verification_email']);
                
                // Set success message
                $verification_success = $result['message'];
                
                // Redirect to home page after 2 seconds
                header("refresh:2;url=../index.php");
            } else {
                $verification_err = $result['message'];
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Email Verification</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($verification_err)) : ?>
                        <div class="alert alert-danger"><?php echo $verification_err; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($verification_success)) : ?>
                        <div class="alert alert-success"><?php echo $verification_success; ?></div>
                    <?php endif; ?>
                    
                    <p>A verification code has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>. Please enter the code below to complete your registration.</p>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="otp" class="form-label">Verification Code</label>
                            <input type="text" name="otp" id="otp" class="form-control <?php echo (!empty($otp_err)) ? 'is-invalid' : ''; ?>" value="" placeholder="Enter the 6-digit code from your email" maxlength="6">
                            <div class="invalid-feedback"><?php echo $otp_err; ?></div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </div>
                        
                        <div class="text-center">
                            <p>Didn't receive the code? 
                                <button type="submit" name="resend_otp" class="btn btn-link p-0">Resend Code</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>