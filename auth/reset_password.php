<?php
// Password Reset page for PetStopBD with smooth transitions

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
$email = $token = $password = $confirm_password = "";
$email_err = $token_err = $password_err = $confirm_password_err = "";
$reset_err = $reset_success = "";

// Check current step
$step = isset($_POST['step']) ? $_POST['step'] : 'request';

// Check if token is provided in URL for backward compatibility
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $step = 'verify';
    $token = $_GET['token'];
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if ($step == 'request') {
        // Process password reset request
        
        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter an email.";
        } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email.";
        } else {
            $email = trim($_POST["email"]);
        }
        
        // Check input errors before requesting reset
        if (empty($email_err)) {
            // Request password reset
            $result = request_password_reset($conn, $email);
            
            if ($result['success']) {
                // Send password reset email
                if (send_password_reset_email($email, $result['token'])) {
                    $_SESSION['reset_email'] = $email;
                    $reset_success = "A reset code has been sent to your email address.";
                    $step = 'verify'; // Move to verification step
                } else {
                    $reset_err = "Failed to send reset email. Please try again.";
                }
            } else {
                $reset_err = $result['message'];
            }
        }
    } 
    elseif ($step == 'verify') {
        // Process code verification
        
        if (isset($_POST['resend_code'])) {
            // Resend reset code
            $email = $_SESSION['reset_email'] ?? trim($_POST['email'] ?? '');
            
            if (!empty($email)) {
                $result = request_password_reset($conn, $email);
                
                if ($result['success']) {
                    if (send_password_reset_email($email, $result['token'])) {
                        $reset_success = "A new reset code has been sent to your email.";
                    } else {
                        $reset_err = "Failed to send reset email. Please try again.";
                    }
                } else {
                    $reset_err = $result['message'];
                }
            } else {
                $reset_err = "Session expired. Please start over.";
                $step = 'request';
            }
        } else {
            // Validate token/code
            if (empty(trim($_POST["token"]))) {
                $token_err = "Please enter the reset code.";
            } else {
                // Remove any hyphens if user copies formatted code
                $token = str_replace('-', '', trim($_POST["token"]));
            }
            
            // Check input errors
            if (empty($token_err)) {
                // Verify the token exists and is valid
                $stmt = $conn->prepare("SELECT user_id FROM password_reset WHERE token = ? AND expiry > NOW()");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 0) {
                    $token_err = "Invalid or expired reset code. Please try again.";
                } else {
                    // Token is valid, store in session and proceed to password reset form
                    $_SESSION['reset_token'] = $token;
                    $reset_success = "Code verified successfully. Please set your new password.";
                    $step = 'reset'; // Move to password reset step
                }
            }
        }
    }
    elseif ($step == 'reset') {
        // Process password reset
        
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
        
        // Check if token is in session
        if (!isset($_SESSION['reset_token'])) {
            $reset_err = "Reset session expired. Please start over.";
            $step = 'request';
        } else {
            $token = $_SESSION['reset_token'];
        }
        
        // Check input errors before resetting password
        if (empty($password_err) && empty($confirm_password_err) && empty($reset_err)) {
            // Reset password
            $result = reset_password($conn, $token, $password);
            
            if ($result['success']) {
                // Clear reset token from session
                unset($_SESSION['reset_token']);
                unset($_SESSION['reset_email']);
                
                $_SESSION['password_reset_successful'] = true;
                
                $reset_success = $result['message'];
                header("refresh:1;url=login.php");
            } else {
                $reset_err = $result['message'];
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?php 
                            switch($step) {
                                case 'request': echo 'Reset Password'; break;
                                case 'verify': echo 'Enter Code'; break;
                                case 'reset': echo 'New Password'; break;
                            }
                            ?>
                        </h5>
                        <div class="step-indicator">
                            <span class="badge <?php echo ($step == 'request') ? 'bg-light text-primary' : 'bg-success'; ?> step-badge">1</span>
                            <span class="mx-1">→</span>
                            <span class="badge <?php echo ($step == 'verify') ? 'bg-light text-primary' : ($step == 'reset' ? 'bg-success' : 'bg-secondary'); ?> step-badge">2</span>
                            <span class="mx-1">→</span>
                            <span class="badge <?php echo ($step == 'reset') ? 'bg-light text-primary' : 'bg-secondary'; ?> step-badge">3</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Request Reset Form -->
                    <div id="request-form" style="display: <?php echo ($step == 'request') ? 'block' : 'none'; ?>;">
                        
                        <?php if (!empty($reset_err)) : ?>
                            <div class="alert alert-danger alert-sm py-2"><?php echo $reset_err; ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-3">
                            <i class="fas fa-key fa-2x text-primary mb-2"></i>
                            <p class="mb-0">Enter your email to receive a reset code</p>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="step" value="request">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="Enter your email">
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Send Reset Code</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Verify Code Form -->
                    <div id="verify-form" style="display: <?php echo ($step == 'verify') ? 'block' : 'none'; ?>;">
                        
                        <?php if (!empty($reset_success)) : ?>
                            <div class="alert alert-success alert-sm py-2">
                                <i class="fas fa-check-circle me-2"></i><?php echo $reset_success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($reset_err)) : ?>
                            <div class="alert alert-danger alert-sm py-2"><?php echo $reset_err; ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-3">
                            <p class="mb-1">Reset code sent to:</p>
                            <p class="fw-bold text-primary mb-0"><?php echo htmlspecialchars($_SESSION['reset_email'] ?? $email); ?></p>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="step" value="verify">
                            
                            <div class="mb-3">
                                <label for="token" class="form-label">Reset Code</label>
                                <input type="text" name="token" id="token" class="form-control text-center <?php echo (!empty($token_err)) ? 'is-invalid' : ''; ?>" value="" placeholder="Enter reset code" style="font-size: 1.1rem; letter-spacing: 0.2rem; height: 45px;">
                                <div class="invalid-feedback"><?php echo $token_err; ?></div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Verify Code</button>
                            </div>
                            
                            <div class="text-center">
                                <small class="text-muted">Didn't receive the code?</small><br>
                                <button type="submit" name="resend_code" class="btn btn-outline-primary btn-sm mt-1">Resend Code</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Reset Password Form -->
                    <div id="reset-form" style="display: <?php echo ($step == 'reset') ? 'block' : 'none'; ?>;">
                        
                        <?php if (!empty($reset_success)) : ?>
                            <div class="alert alert-success alert-sm py-2"><?php echo $reset_success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($reset_err)) : ?>
                            <div class="alert alert-danger alert-sm py-2"><?php echo $reset_err; ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-3">
                            <i class="fas fa-lock fa-2x text-success mb-2"></i>
                            <p class="mb-0">Choose a strong password</p>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="step" value="reset">
                            
                            <div class="mb-2">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter new password">
                                <div class="invalid-feedback"><?php echo $password_err; ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirm new password">
                                <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Update Password</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Navigation -->
                    <div class="text-center mt-3">
                        <small><a href="login.php">← Back to Login</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.step-badge {
    width: 20px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.7rem;
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
        width: 18px;
        height: 18px;
        font-size: 0.65rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on appropriate input based on current step
    const step = '<?php echo $step; ?>';
    
    if (step === 'request') {
        document.getElementById('email')?.focus();
    } else if (step === 'verify') {
        document.getElementById('token')?.focus();
    } else if (step === 'reset') {
        document.getElementById('password')?.focus();
    }
    
    // Format token input
    const tokenInput = document.getElementById('token');
    if (tokenInput) {
        tokenInput.addEventListener('input', function(e) {
            // Allow alphanumeric characters only
            this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
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