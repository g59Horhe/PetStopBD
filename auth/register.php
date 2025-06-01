<?php
// Registration page for PetStopBD with modern design

// Include necessary files
require_once '../config/db_connect.php';
require_once '../includes/auth_functions.php';
require_once '../includes/mail_functions.php';

// Start session
session_start();

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit();
}

// Define variables and initialize with empty values
$username = $email = $password = $confirm_password = $first_name = $last_name = "";
$username_err = $email_err = $password_err = $confirm_password_err = $first_name_err = $last_name_err = "";
$registration_err = $registration_success = "";
$otp = $otp_err = $verification_err = $verification_success = "";

// Check current step
$step = isset($_POST['step']) ? $_POST['step'] : 'register';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if ($step == 'register') {
        // Process registration form
        
        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter a username.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
            $username_err = "Username can only contain letters, numbers, and underscores.";
        } else {
            $username = trim($_POST["username"]);
        }
        
        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter an email.";
        } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email.";
        } else {
            $email = trim($_POST["email"]);
        }
        
        // Validate first name
        if (empty(trim($_POST["first_name"]))) {
            $first_name_err = "Please enter your first name.";
        } else {
            $first_name = trim($_POST["first_name"]);
        }
        
        // Validate last name
        if (empty(trim($_POST["last_name"]))) {
            $last_name_err = "Please enter your last name.";
        } else {
            $last_name = trim($_POST["last_name"]);
        }
        
        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter a password.";
        } elseif (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($password_err) && ($password != $confirm_password)) {
                $confirm_password_err = "Passwords did not match.";
            }
        }
        
        // Check input errors before inserting into database
        if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($first_name_err) && empty($last_name_err)) {
            
            // Register user
            $result = register_user($conn, $username, $email, $password, $first_name, $last_name);
            
            if ($result['success']) {
                // Send OTP to email
                if (send_otp_email($email, $result['otp'])) {
                    // Store email in session for verification
                    $_SESSION['verification_email'] = $email;
                    
                    $registration_success = "A verification code has been sent to your email address.";
                    $step = 'verify'; // Move to verification step
                } else {
                    $registration_err = "Failed to send verification email. Please try again.";
                }
            } else {
                $registration_err = $result['message'];
            }
        }
    } 
    elseif ($step == 'verify') {
        // Process OTP verification
        
        $email = $_SESSION['verification_email'] ?? '';
        
        if (isset($_POST['resend_otp'])) {
            // Resend OTP
            $new_otp = generate_otp();
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            $stmt = $conn->prepare("UPDATE user_verification SET otp = ?, otp_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $new_otp, $otp_expiry, $email);
            
            if ($stmt->execute()) {
                if (send_otp_email($email, $new_otp)) {
                    $verification_success = "A new verification code has been sent to your email.";
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
                    
                    // Set success message and redirect
                    $verification_success = $result['message'];
                    header("refresh:1;url=../index.php");
                } else {
                    $verification_err = $result['message'];
                }
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?php echo ($step == 'verify') ? 'Verify Email' : 'Create Account'; ?>
                        </h5>
                        <div class="step-indicator">
                            <span class="badge <?php echo ($step == 'register') ? 'bg-light text-primary' : 'bg-success'; ?> step-badge">1</span>
                            <span class="mx-1">→</span>
                            <span class="badge <?php echo ($step == 'verify') ? 'bg-light text-primary' : 'bg-secondary'; ?> step-badge">2</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Registration Form -->
                    <div id="registration-form" style="display: <?php echo ($step == 'register') ? 'block' : 'none'; ?>;">
                        
                        <?php if (!empty($registration_err)) : ?>
                            <div class="alert alert-danger"><?php echo $registration_err; ?></div>
                        <?php endif; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="step" value="register">
                            
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" name="first_name" id="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $first_name; ?>">
                                    <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                                </div>
                                
                                <div class="col-md-6 mb-2">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $last_name; ?>">
                                    <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                <div class="invalid-feedback"><?php echo $username_err; ?></div>
                            </div>
                            
                            <div class="mb-2">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
                            </div>
                            
                            <div class="mb-2">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $password_err; ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
                                <label for="terms" class="form-check-label"><small>I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a></small></label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Create Account</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Verification Form -->
                    <div id="verification-form" style="display: <?php echo ($step == 'verify') ? 'block' : 'none'; ?>;">
                        
                        <?php if (!empty($registration_success)) : ?>
                            <div class="alert alert-success alert-sm py-2">
                                <i class="fas fa-check-circle me-2"></i><?php echo $registration_success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($verification_err)) : ?>
                            <div class="alert alert-danger alert-sm py-2"><?php echo $verification_err; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($verification_success)) : ?>
                            <div class="alert alert-success alert-sm py-2"><?php echo $verification_success; ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-3">
                            <p class="mb-1">We've sent a verification code to:</p>
                            <p class="fw-bold text-primary mb-0"><?php echo htmlspecialchars($_SESSION['verification_email'] ?? $email); ?></p>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="step" value="verify">
                            
                            <div class="mb-3">
                                <label for="otp" class="form-label">Verification Code</label>
                                <input type="text" name="otp" id="otp" class="form-control text-center <?php echo (!empty($otp_err)) ? 'is-invalid' : ''; ?>" value="" placeholder="Enter 6-digit code" maxlength="6" style="font-size: 1.2rem; letter-spacing: 0.3rem; height: 45px;">
                                <div class="invalid-feedback"><?php echo $otp_err; ?></div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Verify & Complete Registration</button>
                            </div>
                            
                            <div class="text-center">
                                <small class="text-muted">Didn't receive the code?</small><br>
                                <button type="submit" name="resend_otp" class="btn btn-outline-primary btn-sm mt-1">Resend Code</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Navigation -->
                    <div class="text-center mt-3">
                        <small>Already have an account? <a href="login.php">Login here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Acceptance of Terms</h5>
                <p>By registering for an account on PetStopBD, you agree to be bound by these Terms and Conditions.</p>
                
                <h5>2. User Responsibilities</h5>
                <p>Users are responsible for maintaining the confidentiality of their account information and for all activities that occur under their account.</p>
                
                <h5>3. Content Policy</h5>
                <p>Users agree not to post content that is illegal, harmful, threatening, abusive, harassing, defamatory, vulgar, obscene, invasive of another's privacy, or otherwise objectionable.</p>
                
                <h5>4. Animal Welfare</h5>
                <p>PetStopBD is committed to animal welfare. Users agree to follow ethical practices when posting about pets for adoption, rescue, or any other services.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.step-badge {
    width: 22px;
    height: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.75rem;
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
    border-color: #1E88E5;
}

.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
}

.form-control {
    border-radius: 6px;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.fade-in {
    animation: fadeIn 0.4s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 576px) {
    .card-body {
        padding: 1.5rem !important;
    }
    
    .step-indicator {
        font-size: 0.8rem;
    }
    
    .step-badge {
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on OTP input when verification form is shown
    const otpInput = document.getElementById('otp');
    const verificationForm = document.getElementById('verification-form');
    
    if (verificationForm && verificationForm.style.display === 'block' && otpInput) {
        otpInput.focus();
    }
    
    // Format OTP input
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        otpInput.addEventListener('keyup', function(e) {
            // Auto-submit when 6 digits are entered
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>