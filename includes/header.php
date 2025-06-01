<?php
// Initialize authentication if not already done
if (!function_exists('is_logged_in')) {
    require_once dirname(__DIR__) . '/includes/auth_functions.php';
    start_session_if_not_started();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetStopBD - Your One Stop Pet Solution</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']) == '/petstopbd' ? 'css/style.css' : '../css/style.css'; ?>">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo dirname($_SERVER['PHP_SELF']) == '/petstopbd' ? 'index.php' : '../index.php'; ?>">
                <span class="text-primary fw-bold">PetStop</span><span class="text-danger">BD</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo dirname($_SERVER['PHP_SELF']) == '/petstopbd' ? 'index.php' : '../index.php'; ?>">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Rescue
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Volunteer</a></li>
                            <li><a class="dropdown-item" href="#">Report Cruelty</a></li>
                            <li><a class="dropdown-item" href="#">Lost & Found</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Owner
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Pet Profiles</a></li>
                            <li><a class="dropdown-item" href="#">Pet Shops</a></li>
                            <li><a class="dropdown-item" href="#">Pet Services</a></li>
                            <li><a class="dropdown-item" href="#">Foster Homes</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Adoption
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Adoption Listings</a></li>
                            <li><a class="dropdown-item" href="#">Request Adoption</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Vet+Pharmacy
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Find Vet</a></li>
                            <li><a class="dropdown-item" href="#">Online Pharmacy</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Blog</a>
                    </li>
                    
                    <?php if (is_logged_in()) : ?>
                        <!-- Show user dropdown when logged in -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-paw me-2"></i>My Pets</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-heart me-2"></i>Favorites</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo dirname($_SERVER['PHP_SELF']) == '/petstopbd' ? 'auth/logout.php' : '../auth/logout.php'; ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else : ?>
                        <!-- Show login/register button when not logged in -->
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-lg-2 px-3" href="<?php echo dirname($_SERVER['PHP_SELF']) == '/petstopbd' ? 'auth/login.php' : '../auth/login.php'; ?>">Login/Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <main>
