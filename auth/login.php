<?php
// Login page for PetStopBD

// Include database connection
require_once '../config/db_connect.php';
require_once '../includes/auth_functions.php';

// Start session
session_start();

// Check if redirected from password reset
$password_reset_success = false;
if (isset($_SESSION['password_reset_successful']) && $_SESSION['password_reset_successful'] === true) {
    $password_reset_success = true;
    // Clear the flag
    unset($_SESSION['password_reset_successful']);
}

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit();
}

// Define variables and initialize with empty values
$username_email = $password = "";
$username_email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate username/email
    if (empty(trim($_POST["username_email"]))) {
        $username_email_err = "Please enter username or email.";
    } else {
        $username_email = trim($_POST["username_email"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Check input errors before logging in
    if (empty($username_email_err) && empty($password_err)) {
        // Attempt to log in
        $result = login_user($conn, $username_email, $password);
        
        if ($result['success']) {
            // Redirect to home page
            header("location: ../index.php");
        } else {
            $login_err = $result['message'];
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
                                    <h6>Pet Adoption</h6>
                                    <p>Find your perfect companion</p>
                                </div>
                            </div>
                            
                            <div class="feature-item" data-aos="fade-right" data-aos-delay="300">
                                <div class="feature-icon">
                                    <i class="fas fa-hand-holding-heart"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Animal Rescue</h6>
                                    <p>Help save lives together</p>
                                </div>
                            </div>
                            
                            <div class="feature-item" data-aos="fade-right" data-aos-delay="400">
                                <div class="feature-icon">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Vet Services</h6>
                                    <p>Professional pet healthcare</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="testimonial-mini" data-aos="fade-right" data-aos-delay="500">
                            <div class="testimonial-content">
                                <p>"PetStopBD helped me find my perfect companion. Amazing platform!"</p>
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
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-6">
                <div class="auth-form-container">
                    <div class="auth-form-wrapper" data-aos="fade-left" data-aos-delay="200">
                        
                        <!-- Mobile Brand Header -->
                        <div class="mobile-brand d-lg-none text-center mb-4">
                            <h2 class="brand-title">
                                <span class="text-primary">PetStop</span><span class="text-danger">BD</span>
                            </h2>
                        </div>
                        
                        <div class="auth-header text-center mb-4">
                            <h3 class="auth-title">Welcome Back!</h3>
                            <p class="auth-subtitle">Sign in to your account to continue</p>
                        </div>
                        
                        <!-- Success Messages -->
                        <?php if ($password_reset_success) : ?>
                            <div class="alert alert-success alert-modern" data-aos="fade-in">
                                <i class="fas fa-check-circle me-2"></i>
                                Password has been reset successfully. You can now login with your new password.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($login_err)) : ?>
                            <div class="alert alert-danger alert-modern" data-aos="shake">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $login_err; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                            <div class="form-group-modern">
                                <div class="form-floating">
                                    <input type="text" 
                                           name="username_email" 
                                           id="username_email" 
                                           class="form-control form-control-modern <?php echo (!empty($username_email_err)) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo $username_email; ?>"
                                           placeholder="Username or Email">
                                    <label for="username_email">
                                        <i class="fas fa-user me-2"></i>Username or Email
                                    </label>
                                    <div class="invalid-feedback"><?php echo $username_email_err; ?></div>
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
                            
                            <div class="form-options">
                                <div class="form-check-modern">
                                    <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input">
                                    <label for="remember_me" class="form-check-label">Remember me</label>
                                </div>
                                <a href="reset_password.php" class="forgot-link">Forgot Password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-auth btn-primary">
                                <span class="btn-text">Sign In</span>
                                <span class="btn-loader">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </button>
                            
                            <div class="auth-divider">
                                <span>or</span>
                            </div>
                            
                            <div class="social-login">
                                <button type="button" class="btn btn-social btn-google">
                                    <i class="fab fa-google me-2"></i>Continue with Google
                                </button>
                                <button type="button" class="btn btn-social btn-facebook">
                                    <i class="fab fa-facebook-f me-2"></i>Continue with Facebook
                                </button>
                            </div>
                            
                            <div class="auth-footer text-center">
                                <p>Don't have an account? <a href="register.php" class="auth-link">Create Account</a></p>
                            </div>
                        </form>
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
/* Modern Auth Styles */
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
    max-width: 400px;
}

.mobile-brand .brand-title {
    font-size: 2rem;
    font-weight: 800;
}

.auth-header {
    margin-bottom: 2rem;
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

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.form-check-modern {
    display: flex;
    align-items: center;
}

.form-check-input {
    margin-right: 0.5rem;
    border-radius: 4px;
}

.form-check-label {
    color: #4a5568;
    font-size: 0.9rem;
    margin: 0;
}

.forgot-link {
    color: var(--auth-primary);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.forgot-link:hover {
    color: var(--auth-secondary);
    text-decoration: none;
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

.auth-divider {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e2e8f0;
}

.auth-divider span {
    background: rgba(248, 250, 255, 0.95);
    padding: 0 1rem;
    color: #718096;
    font-size: 0.9rem;
}

.social-login {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.btn-social {
    width: 100%;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    background: white;
    color: #4a5568;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-social:hover {
    border-color: #cbd5e0;
    transform: translateY(-1px);
    box-shadow: var(--shadow-light);
    color: #2d3748;
}

.btn-google:hover {
    border-color: #ea4335;
    color: #ea4335;
}

.btn-facebook:hover {
    border-color: #1877f2;
    color: #1877f2;
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
    
    .social-login {
        flex-direction: column;
    }
    
    .auth-title {
        font-size: 1.5rem;
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
document.querySelector('.auth-form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-auth');
    submitBtn.classList.add('loading');
    
    // Remove loading state after 3 seconds (fallback)
    setTimeout(() => {
        submitBtn.classList.remove('loading');
    }, 3000);
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

// Auto-focus first input
document.addEventListener('DOMContentLoaded', function() {
    const firstInput = document.getElementById('username_email');
    if (firstInput) {
        setTimeout(() => {
            firstInput.focus();
        }, 500);
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>