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
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Join Our Community</h6>
                                    <p>Connect with pet lovers across Bangladesh</p>
                                </div>
                            </div>
                            
                            <div class="feature-item" data-aos="fade-right" data-aos-delay="300">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Safe & Secure</h6>
                                    <p>Your data is protected with us</p>
                                </div>
                            </div>
                            
                            <div class="feature-item" data-aos="fade-right" data-aos-delay="400">
                                <div class="feature-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Growing Community</h6>
                                    <p>5000+ active members and counting</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="testimonial-mini" data-aos="fade-right" data-aos-delay="500">
                            <div class="testimonial-content">
                                <p>"Joining PetStopBD was the best decision! I found my furry friend and amazing community."</p>
                                <div class="testimonial-author">
                                    <img src="../img/testimonial-1.jpg" alt="User" class="author-avatar">
                                    <div>
                                        <h6>Sarah Ahmed</h6>
                                        <small>Happy Pet Owner</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Registration Form -->
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
                                    <div class="step-dot <?php echo ($step == 'register') ? 'active' : ($step == 'verify' ? 'completed' : ''); ?>">1</div>
                                    <div class="step-line <?php echo ($step == 'verify') ? 'active' : ''; ?>"></div>
                                    <div class="step-dot <?php echo ($step == 'verify') ? 'active' : ''; ?>">2</div>
                                </div>
                                <div class="step-labels">
                                    <span class="step-label <?php echo ($step == 'register') ? 'active' : ''; ?>">Account Info</span>
                                    <span class="step-label <?php echo ($step == 'verify') ? 'active' : ''; ?>">Verification</span>
                                </div>
                            </div>
                            
                            <?php if ($step == 'register') : ?>
                                <h3 class="auth-title">Create Your Account</h3>
                                <p class="auth-subtitle">Join thousands of pet lovers in Bangladesh</p>
                            <?php else : ?>
                                <h3 class="auth-title">Verify Your Email</h3>
                                <p class="auth-subtitle">Enter the code we sent to your email</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Registration Form -->
                        <div id="registration-form" style="display: <?php echo ($step == 'register') ? 'block' : 'none'; ?>;">
                            
                            <?php if (!empty($registration_err)) : ?>
                                <div class="alert alert-danger alert-modern" data-aos="shake">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $registration_err; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                                <input type="hidden" name="step" value="register">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <div class="form-floating">
                                                <input type="text" 
                                                       name="first_name" 
                                                       id="first_name" 
                                                       class="form-control form-control-modern <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" 
                                                       value="<?php echo $first_name; ?>"
                                                       placeholder="First Name">
                                                <label for="first_name">
                                                    <i class="fas fa-user me-2"></i>First Name
                                                </label>
                                                <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <div class="form-floating">
                                                <input type="text" 
                                                       name="last_name" 
                                                       id="last_name" 
                                                       class="form-control form-control-modern <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" 
                                                       value="<?php echo $last_name; ?>"
                                                       placeholder="Last Name">
                                                <label for="last_name">
                                                    <i class="fas fa-user me-2"></i>Last Name
                                                </label>
                                                <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group-modern">
                                    <div class="form-floating">
                                        <input type="text" 
                                               name="username" 
                                               id="username" 
                                               class="form-control form-control-modern <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                                               value="<?php echo $username; ?>"
                                               placeholder="Username">
                                        <label for="username">
                                            <i class="fas fa-at me-2"></i>Username
                                        </label>
                                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                                    </div>
                                </div>
                                
                                <div class="form-group-modern">
                                    <div class="form-floating">
                                        <input type="email" 
                                               name="email" 
                                               id="email" 
                                               class="form-control form-control-modern <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                               value="<?php echo $email; ?>"
                                               placeholder="Email">
                                        <label for="email">
                                            <i class="fas fa-envelope me-2"></i>Email Address
                                        </label>
                                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                    </div>
                                </div>
                                
                                <div class="form-group-modern">
                                    <div class="form-floating">
                                        <input type="password" 
                                               name="password" 
                                               id="password" 
                                               class="form-control form-control-modern <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                                               placeholder="Password">
                                        <label for="password">
                                            <i class="fas fa-lock me-2"></i>Password
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
                                
                                <div class="form-check-modern mb-3">
                                    <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
                                    <label for="terms" class="form-check-label">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-auth btn-primary">
                                    <span class="btn-text">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </span>
                                    <span class="btn-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Verification Form -->
                        <div id="verification-form" style="display: <?php echo ($step == 'verify') ? 'block' : 'none'; ?>;">
                            
                            <?php if (!empty($registration_success)) : ?>
                                <div class="alert alert-success alert-modern" data-aos="fade-in">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $registration_success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($verification_err)) : ?>
                                <div class="alert alert-danger alert-modern" data-aos="shake">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $verification_err; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($verification_success)) : ?>
                                <div class="alert alert-success alert-modern" data-aos="fade-in">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $verification_success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="verification-info text-center mb-4">
                                <div class="verification-icon">
                                    <i class="fas fa-envelope-open-text"></i>
                                </div>
                                <p class="mb-1">We've sent a 6-digit code to:</p>
                                <p class="email-display"><?php echo htmlspecialchars($_SESSION['verification_email'] ?? $email); ?></p>
                            </div>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                                <input type="hidden" name="step" value="verify">
                                
                                <div class="form-group-modern">
                                    <div class="otp-input-container">
                                        <input type="text" 
                                               name="otp" 
                                               id="otp" 
                                               class="form-control otp-input <?php echo (!empty($otp_err)) ? 'is-invalid' : ''; ?>" 
                                               value="" 
                                               placeholder="Enter 6-digit code" 
                                               maxlength="6"
                                               autocomplete="off">
                                        <div class="invalid-feedback"><?php echo $otp_err; ?></div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-auth btn-primary">
                                    <span class="btn-text">
                                        <i class="fas fa-check me-2"></i>Verify & Complete
                                    </span>
                                    <span class="btn-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                                
                                <div class="resend-section text-center mt-3">
                                    <p class="resend-text">Didn't receive the code?</p>
                                    <button type="submit" name="resend_otp" class="btn btn-link resend-btn">
                                        <i class="fas fa-redo me-1"></i>Resend Code
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Navigation -->
                        <div class="auth-footer text-center mt-4">
                            <p>Already have an account? <a href="login.php" class="auth-link">Sign in here</a></p>
                        </div>
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

<!-- Add AOS and modern auth styles -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
/* Modern Auth Styles - Same as login page */
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
    max-width: 450px;
}

.mobile-brand .brand-title {
    font-size: 2rem;
    font-weight: 800;
}

.auth-header {
    margin-bottom: 2rem;
}

/* Step Indicator */
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
    margin: 0 1rem;
    transition: all 0.3s ease;
}

.step-line.active {
    background: var(--auth-primary);
}

.step-labels {
    display: flex;
    justify-content: space-between;
}

.step-label {
    font-size: 0.8rem;
    color: #718096;
    transition: color 0.3s ease;
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

.form-check-modern {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.form-check-modern .form-check-input {
    margin-right: 0.5rem;
    border-radius: 4px;
}

.form-check-modern .form-check-label {
    color: #4a5568;
    font-size: 0.9rem;
    margin: 0;
}

.form-check-modern a {
    color: var(--auth-primary);
    text-decoration: none;
}

.form-check-modern a:hover {
    color: var(--auth-secondary);
    text-decoration: underline;
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
}

.otp-input-container {
    text-align: center;
}

.otp-input {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: 0.5rem;
    height: 60px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.otp-input:focus {
    border-color: var(--auth-primary);
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
    background: white;
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
    
    .step-labels {
        padding: 0 0.5rem;
    }
}

/* Animation Enhancements */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.form-control-modern:focus {
    animation: pulse 0.3s ease;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* OTP Input Animation */
.otp-input:focus {
    animation: pulse 0.3s ease;
}

/* Step transition animations */
.step-dot, .step-line {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.verification-icon {
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
document.querySelectorAll('.form-control-modern').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentNode.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
        this.parentNode.classList.remove('focused');
    });
});

// OTP input formatting and auto-submit
const otpInput = document.getElementById('otp');
if (otpInput) {
    otpInput.addEventListener('input', function(e) {
        // Remove any non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Add visual feedback
        if (this.value.length > 0) {
            this.style.borderColor = 'var(--auth-primary)';
        }
    });
    
    otpInput.addEventListener('keyup', function(e) {
        // Auto-submit when 6 digits are entered
        if (this.value.length === 6) {
            // Add small delay for better UX
            setTimeout(() => {
                this.form.submit();
            }, 300);
        }
    });
    
    // Auto-focus OTP input when verification form is shown
    const verificationForm = document.getElementById('verification-form');
    if (verificationForm && verificationForm.style.display === 'block') {
        setTimeout(() => {
            otpInput.focus();
        }, 500);
    }
}

// Auto-focus first input on registration form
document.addEventListener('DOMContentLoaded', function() {
    const registrationForm = document.getElementById('registration-form');
    if (registrationForm && registrationForm.style.display === 'block') {
        const firstInput = document.getElementById('first_name');
        if (firstInput) {
            setTimeout(() => {
                firstInput.focus();
            }, 500);
        }
    }
});

// Smooth form transitions
function showForm(formId) {
    const forms = ['registration-form', 'verification-form'];
    
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

// Real-time password confirmation validation
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');

if (passwordInput && confirmPasswordInput) {
    function checkPasswordMatch() {
        if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.nextElementSibling.textContent = 'Passwords do not match';
        } else {
            confirmPasswordInput.classList.remove('is-invalid');
        }
    }
    
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    passwordInput.addEventListener('input', checkPasswordMatch);
}
</script>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>