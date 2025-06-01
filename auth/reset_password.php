<?php
// Password Reset page for PetStopBD with modern design

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
                header("refresh:2;url=login.php");
            } else {
                $reset_err = $result['message'];
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<!-- Modern Auth Background -->
<div class="auth-container">
    <div class="auth-background">
        <div class="auth-gradient"></div>
        <div class="floating-elements">
            <div class="element element-1"></div>
            <div class="element element-2"></div>
            <div class="element element-3"></div>
            <div class="element element-4"></div>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-none d-lg-flex">
                <div class="auth-branding">
                    <div class="branding-content">
                        <div class="brand-logo" data-aos="fade-right" data-aos-delay="100">
                            <h1 class="brand-title">
                                <span class="text-primary">PetStop</span><span class="text-danger">BD</span>
                            </h1>
                            <p class="brand-subtitle">Your One-Stop Pet Solution</p>
                        </div>
                        
                        <div class="features-preview" data-aos="fade-right" data-aos-delay="200">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Secure Reset</h6>
                                    <p>Your account security is our top priority</p>
                                </div>
                            </div>
                            
                            <div class="feature-item" data-aos="fade-right" data-aos-delay="300">
                                <div class="feature-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Quick Process</h6>
                                    <p>Reset your password in just a few steps</p>
                                </div>
                            </div>
                            
                            <div class="feature-item" data-aos="fade-right" data-aos-delay="400">
                                <div class="feature-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Email Verification</h6>
                                    <p>We'll send a secure code to your email</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="testimonial-mini" data-aos="fade-right" data-aos-delay="500">
                            <div class="testimonial-content">
                                <p>"The password reset process was so smooth and secure. I was back to helping pets in no time!"</p>
                                <div class="testimonial-author">
                                    <img src="../img/testimonial-2.jpg" alt="User" class="author-avatar">
                                    <div>
                                        <h6>Karim Rahman</h6>
                                        <small>Volunteer</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Reset Form -->
            <div class="col-lg-6">
                <div class="auth-form-container">
                    <div class="auth-form-wrapper" data-aos="fade-left" data-aos-delay="200">
                        
                        <!-- Mobile Brand Header -->
                        <div class="mobile-brand d-lg-none text-center mb-4">
                            <h2 class="brand-title">
                                <span class="text-primary">PetStop</span><span class="text-danger">BD</span>
                            </h2>
                        </div>
                        
                        <!-- Dynamic Header -->
                        <div class="auth-header text-center mb-4">
                            <div class="step-indicator mb-3">
                                <div class="step-dots">
                                    <div class="step-dot <?php echo ($step == 'request') ? 'active' : (($step == 'verify' || $step == 'reset') ? 'completed' : ''); ?>">1</div>
                                    <div class="step-line <?php echo (($step == 'verify' || $step == 'reset')) ? 'active' : ''; ?>"></div>
                                    <div class="step-dot <?php echo ($step == 'verify') ? 'active' : ($step == 'reset' ? 'completed' : ''); ?>">2</div>
                                    <div class="step-line <?php echo ($step == 'reset') ? 'active' : ''; ?>"></div>
                                    <div class="step-dot <?php echo ($step == 'reset') ? 'active' : ''; ?>">3</div>
                                </div>
                                <div class="step-labels">
                                    <span class="step-label <?php echo ($step == 'request') ? 'active' : ''; ?>">Email</span>
                                    <span class="step-label <?php echo ($step == 'verify') ? 'active' : ''; ?>">Verify Code</span>
                                    <span class="step-label <?php echo ($step == 'reset') ? 'active' : ''; ?>">New Password</span>
                                </div>
                            </div>
                            
                            <?php if ($step == 'request') : ?>
                                <h3 class="auth-title">Reset Password</h3>
                                <p class="auth-subtitle">Enter your email to receive a reset code</p>
                            <?php elseif ($step == 'verify') : ?>
                                <h3 class="auth-title">Enter Reset Code</h3>
                                <p class="auth-subtitle">Check your email for the verification code</p>
                            <?php else : ?>
                                <h3 class="auth-title">Create New Password</h3>
                                <p class="auth-subtitle">Choose a strong password for your account</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Request Reset Form -->
                        <div id="request-form" style="display: <?php echo ($step == 'request') ? 'block' : 'none'; ?>;">
                            
                            <?php if (!empty($reset_err) && $step == 'request') : ?>
                                <div class="alert alert-danger alert-modern" data-aos="shake">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $reset_err; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-info text-center mb-4">
                                <div class="form-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <p class="info-text">We'll send a secure reset code to your email address</p>
                            </div>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                                <input type="hidden" name="step" value="request">
                                
                                <div class="form-group-modern">
                                    <div class="form-floating">
                                        <input type="email" 
                                               name="email" 
                                               id="email" 
                                               class="form-control form-control-modern <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                               value="<?php echo $email; ?>" 
                                               placeholder="Enter your email">
                                        <label for="email">
                                            <i class="fas fa-envelope me-2"></i>Email Address
                                        </label>
                                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-auth btn-primary">
                                    <span class="btn-text">
                                        <i class="fas fa-paper-plane me-2"></i>Send Reset Code
                                    </span>
                                    <span class="btn-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Verify Code Form -->
                        <div id="verify-form" style="display: <?php echo ($step == 'verify') ? 'block' : 'none'; ?>;">
                            
                            <?php if (!empty($reset_success) && $step == 'verify') : ?>
                                <div class="alert alert-success alert-modern" data-aos="fade-in">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $reset_success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($reset_err) && $step == 'verify') : ?>
                                <div class="alert alert-danger alert-modern" data-aos="shake">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $reset_err; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="verification-info text-center mb-4">
                                <div class="verification-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <p class="mb-1">Reset code sent to:</p>
                                <p class="email-display"><?php echo htmlspecialchars($_SESSION['reset_email'] ?? $email); ?></p>
                                <small class="text-muted">Check your email and enter the code below</small>
                            </div>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                                <input type="hidden" name="step" value="verify">
                                
                                <div class="form-group-modern">
                                    <div class="token-input-container">
                                        <input type="text" 
                                               name="token" 
                                               id="token" 
                                               class="form-control token-input <?php echo (!empty($token_err)) ? 'is-invalid' : ''; ?>" 
                                               value="" 
                                               placeholder="Enter reset code" 
                                               autocomplete="off">
                                        <div class="invalid-feedback"><?php echo $token_err; ?></div>
                                        <small class="form-text text-muted">Copy the full code from your email</small>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-auth btn-primary">
                                    <span class="btn-text">
                                        <i class="fas fa-check me-2"></i>Verify Code
                                    </span>
                                    <span class="btn-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                                
                                <div class="resend-section text-center mt-3">
                                    <p class="resend-text">Didn't receive the code?</p>
                                    <button type="submit" name="resend_code" class="btn btn-link resend-btn">
                                        <i class="fas fa-redo me-1"></i>Resend Code
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Reset Password Form -->
                        <div id="reset-form" style="display: <?php echo ($step == 'reset') ? 'block' : 'none'; ?>;">
                            
                            <?php if (!empty($reset_success) && $step == 'reset') : ?>
                                <div class="alert alert-success alert-modern" data-aos="fade-in">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $reset_success; ?>
                                    <div class="redirect-info mt-2">
                                        <small><i class="fas fa-info-circle me-1"></i>Redirecting to login page...</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($reset_err) && $step == 'reset') : ?>
                                <div class="alert alert-danger alert-modern" data-aos="shake">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $reset_err; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-info text-center mb-4">
                                <div class="form-icon success">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <p class="info-text">Choose a strong password to secure your account</p>
                            </div>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                                <input type="hidden" name="step" value="reset">
                                
                                <div class="form-group-modern">
                                    <div class="form-floating">
                                        <input type="password" 
                                               name="password" 
                                               id="password" 
                                               class="form-control form-control-modern <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                                               placeholder="New Password">
                                        <label for="password">
                                            <i class="fas fa-lock me-2"></i>New Password
                                        </label>
                                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group-modern">
                                    <div class="form-floating">
                                        <input type="password" 
                                               name="confirm_password" 
                                               id="confirm_password" 
                                               class="form-control form-control-modern <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                                               placeholder="Confirm Password">
                                        <label for="confirm_password">
                                            <i class="fas fa-lock me-2"></i>Confirm Password
                                        </label>
                                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="password-requirements mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Password must be at least 6 characters long
                                    </small>
                                </div>
                                
                                <button type="submit" class="btn btn-auth btn-success">
                                    <span class="btn-text">
                                        <i class="fas fa-key me-2"></i>Update Password
                                    </span>
                                    <span class="btn-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Navigation -->
                        <div class="auth-footer text-center mt-4">
                            <p>Remember your password? <a href="login.php" class="auth-link">Sign in here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add AOS and modern auth styles -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
/* Modern Auth Styles - Same base as other auth pages */
:root {
    --auth-primary: #667eea;
    --auth-secondary: #764ba2;
    --auth-danger: #f5576c;
    --auth-success: #4facfe;
    --auth-warning: #f093fb;
    --glass-bg: rgba(255, 255, 255, 0.15);
    --glass-border: rgba(255, 255, 255, 0.2);
    --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.15);
    --shadow-medium: 0 15px 35px rgba(31, 38, 135, 0.2);
}

.auth-container {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
}

.auth-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-secondary) 100%);
}

.auth-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.8) 100%);
}

.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
}

.element {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 6s ease-in-out infinite;
}

.element-1 {
    width: 100px;
    height: 100px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.element-2 {
    width: 150px;
    height: 150px;
    top: 60%;
    left: 5%;
    animation-delay: 2s;
}

.element-3 {
    width: 80px;
    height: 80px;
    top: 20%;
    right: 15%;
    animation-delay: 4s;
}

.element-4 {
    width: 120px;
    height: 120px;
    bottom: 20%;
    right: 5%;
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

/* Left Side Branding */
.auth-branding {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: white;
    position: relative;
    z-index: 2;
}

.branding-content {
    max-width: 500px;
}

.brand-logo {
    text-align: center;
    margin-bottom: 3rem;
}

.brand-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.brand-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.features-preview {
    margin-bottom: 3rem;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 15px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateX(10px);
    background: rgba(255, 255, 255, 0.2);
}

.feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.2rem;
}

.feature-text h6 {
    font-weight: 600;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.feature-text p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.testimonial-mini {
    padding: 1.5rem;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    backdrop-filter: blur(10px);
}

.testimonial-content p {
    font-style: italic;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.testimonial-author {
    display: flex;
    align-items: center;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 0.75rem;
    object-fit: cover;
}

.testimonial-author h6 {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.testimonial-author small {
    opacity: 0.8;
}

/* Right Side Form */
.auth-form-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 2rem;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 255, 0.95) 100%);
    backdrop-filter: blur(20px);
    position: relative;
    z-index: 2;
}

.auth-form-wrapper {
    width: 100%;
    max-width: 420px;
}

.mobile-brand .brand-title {
    font-size: 2rem;
    font-weight: 800;
}

.auth-header {
    margin-bottom: 2rem;
}

/* Step Indicator - 3 Steps */
.step-indicator {
    margin-bottom: 1.5rem;
}

.step-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.step-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #718096;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.step-dot.active {
    background: var(--auth-primary);
    color: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
}

.step-dot.completed {
    background: var(--auth-success);
    color: white;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e2e8f0;
    margin: 0 0.75rem;
    transition: all 0.3s ease;
}

.step-line.active {
    background: var(--auth-primary);
}

.step-labels {
    display: flex;
    justify-content: space-between;
    padding: 0 0.25rem;
}

.step-label {
    font-size: 0.75rem;
    color: #718096;
    transition: color 0.3s ease;
    text-align: center;
    flex: 1;
}

.step-label.active {
    color: var(--auth-primary);
    font-weight: 600;
}

.auth-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.auth-subtitle {
    color: #718096;
    margin: 0;
    font-size: 1rem;
}

.alert-modern {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    border: none;
    margin-bottom: 1.5rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.alert-success {
    background: rgba(79, 172, 254, 0.1);
    color: #0369a1;
    border: 1px solid rgba(79, 172, 254, 0.2);
}

.alert-danger {
    background: rgba(245, 87, 108, 0.1);
    color: #dc2626;
    border: 1px solid rgba(245, 87, 108, 0.2);
}

.redirect-info {
    opacity: 0.8;
}

.auth-form {
    width: 100%;
}

.form-group-modern {
    margin-bottom: 1.5rem;
    position: relative;
}

.form-floating {
    position: relative;
}

.form-control-modern {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1rem 1rem 3rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
}

.form-control-modern:focus {
    border-color: var(--auth-primary);
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
    background: white;
}

.form-floating label {
    padding-left: 3rem;
    color: #718096;
    transition: all 0.3s ease;
}

.form-floating .form-control:focus ~ label,
.form-floating .form-control:not(:placeholder-shown) ~ label {
    color: var(--auth-primary);
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #718096;
    cursor: pointer;
    transition: color 0.3s ease;
    z-index: 3;
}

.password-toggle:hover {
    color: var(--auth-primary);
}

/* Form Info Icons */
.form-info {
    margin-bottom: 2rem;
}

.form-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-secondary) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
}

.form-icon.success {
    background: linear-gradient(135deg, var(--auth-success) 0%, #38f9d7 100%);
}

.info-text {
    color: #718096;
    margin: 0;
    font-size: 0.95rem;
}

/* Verification Specific Styles */
.verification-info {
    margin-bottom: 2rem;
}

.verification-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-secondary) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
}

.verification-info p {
    margin-bottom: 0.5rem;
    color: #718096;
}

.email-display {
    font-weight: 600;
    color: var(--auth-primary);
    font-size: 1.1rem;
    word-break: break-all;
}

/* Token Input */
.token-input-container {
    text-align: center;
}

.token-input {
    text-align: center;
    font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.1rem;
    height: 60px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.token-input:focus {
    border-color: var(--auth-primary);
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
    background: white;
}

.password-requirements {
    padding: 0.75rem;
    background: rgba(79, 172, 254, 0.05);
    border-radius: 8px;
    border: 1px solid rgba(79, 172, 254, 0.1);
}

.btn-auth {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 12px;
    border: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.btn-auth.btn-primary {
    background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-secondary) 100%);
    color: white;
}

.btn-auth.btn-success {
    background: linear-gradient(135deg, var(--auth-success) 0%, #38f9d7 100%);
    color: white;
}

.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.btn-loader {
    display: none;
}

.btn-auth.loading .btn-text {
    display: none;
}

.btn-auth.loading .btn-loader {
    display: inline;
}

.resend-section {
    margin-top: 2rem;
}

.resend-text {
    color: #718096;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.resend-btn {
    color: var(--auth-primary);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    padding: 0;
    border: none;
    background: none;
    transition: color 0.3s ease;
}

.resend-btn:hover {
    color: var(--auth-secondary);
    text-decoration: none;
}

.auth-footer p {
    color: #718096;
    margin: 0;
    font-size: 0.95rem;
}

.auth-link {
    color: var(--auth-primary);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.auth-link:hover {
    color: var(--auth-secondary);
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 992px) {
    .brand-title {
        font-size: 2rem;
    }
    
    .auth-form-container {
        background: rgba(255, 255, 255, 0.98);
    }
}

@media (max-width: 768px) {
    .auth-form-container {
        padding: 1rem;
    }
    
    .auth-title {
        font-size: 1.5rem;
    }
    
    .step-dots {
        margin-bottom: 1rem;
    }
    
    .step-dot {
        width: 25px;
        height: 25px;
        font-size: 0.8rem;
    }
    
    .step-line {
        margin: 0 0.5rem;
    }
    
    .step-labels {
        padding: 0;
    }
    
    .step-label {
        font-size: 0.7rem;
    }
}

/* Animation Enhancements */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.form-control-modern:focus, .token-input:focus {
    animation: pulse 0.3s ease;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Step transition animations */
.step-dot, .step-line {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-icon, .verification-icon {
    animation: bounceIn 0.6s ease;
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

/* Success state animations */
.alert-success {
    animation: slideInDown 0.5s ease;
}

@keyframes slideInDown {
    0% {
        transform: translateY(-20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<script>
// Initialize AOS
AOS.init({
    duration: 800,
    once: true,
    offset: 100
});

// Password toggle functionality
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.parentNode.querySelector('.password-toggle i');
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

// Form submission with loading state
document.querySelectorAll('.auth-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.btn-auth');
        if (submitBtn && !submitBtn.name) { // Don't add loading for resend button
            submitBtn.classList.add('loading');
            
            // Remove loading state after 3 seconds (fallback)
            setTimeout(() => {
                submitBtn.classList.remove('loading');
            }, 3000);
        }
    });
});

// Enhanced form interactions
document.querySelectorAll('.form-control-modern, .token-input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentNode.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
        this.parentNode.classList.remove('focused');
    });
});

// Token input formatting
const tokenInput = document.getElementById('token');
if (tokenInput) {
    tokenInput.addEventListener('input', function(e) {
        // Allow alphanumeric characters and hyphens
        this.value = this.value.replace(/[^a-zA-Z0-9-]/g, '');
        
        // Add visual feedback
        if (this.value.length > 0) {
            this.style.borderColor = 'var(--auth-primary)';
        }
    });
    
    tokenInput.addEventListener('paste', function(e) {
        // Handle pasted tokens - clean up any extra formatting
        setTimeout(() => {
            this.value = this.value.replace(/[^a-zA-Z0-9-]/g, '');
        }, 10);
    });
}

// Auto-focus appropriate inputs
document.addEventListener('DOMContentLoaded', function() {
    const currentStep = '<?php echo $step; ?>';
    
    setTimeout(() => {
        if (currentStep === 'request') {
            const emailInput = document.getElementById('email');
            if (emailInput) emailInput.focus();
        } else if (currentStep === 'verify') {
            const tokenInput = document.getElementById('token');
            if (tokenInput) tokenInput.focus();
        } else if (currentStep === 'reset') {
            const passwordInput = document.getElementById('password');
            if (passwordInput) passwordInput.focus();
        }
    }, 500);
});

// Real-time password confirmation validation
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');

if (passwordInput && confirmPasswordInput) {
    function checkPasswordMatch() {
        if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.classList.add('is-invalid');
            const feedback = confirmPasswordInput.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = 'Passwords do not match';
        } else {
            confirmPasswordInput.classList.remove('is-invalid');
        }
    }
    
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    passwordInput.addEventListener('input', checkPasswordMatch);
}

// Add input validation feedback
document.querySelectorAll('input[required]').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
    
    input.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
            this.classList.remove('is-invalid');
        }
    });
});

// Email validation feedback
const emailInput = document.getElementById('email');
if (emailInput) {
    emailInput.addEventListener('blur', function() {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailPattern.test(this.value)) {
            this.classList.add('is-invalid');
            const feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback && !feedback.textContent) {
                feedback.textContent = 'Please enter a valid email address';
            }
        } else {
            this.classList.remove('is-invalid');
        }
    });
}

// Smooth form transitions
function showForm(formId) {
    const forms = ['request-form', 'verify-form', 'reset-form'];
    
    forms.forEach(id => {
        const form = document.getElementById(id);
        if (form) {
            if (id === formId) {
                form.style.display = 'block';
                form.style.opacity = '0';
                setTimeout(() => {
                    form.style.opacity = '1';
                }, 100);
            } else {
                form.style.display = 'none';
            }
        }
    });
}

// Handle successful password reset redirect
<?php if ($step == 'reset' && !empty($reset_success)) : ?>
// Show countdown for redirect
let countdown = 2;
const redirectInfo = document.querySelector('.redirect-info small');
if (redirectInfo) {
    const interval = setInterval(() => {
        countdown--;
        if (countdown > 0) {
            redirectInfo.innerHTML = `<i class="fas fa-info-circle me-1"></i>Redirecting to login page in ${countdown} seconds...`;
        } else {
            redirectInfo.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Redirecting...';
            clearInterval(interval);
        }
    }, 1000);
}
<?php endif; ?>
</script>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>