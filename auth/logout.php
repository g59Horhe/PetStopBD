<?php
// Logout page for PetStopBD

// Include authentication functions
require_once '../includes/auth_functions.php';

// Logout the user
logout_user();

// Redirect to the home page
header("location: ../index.php");
exit;
?>
