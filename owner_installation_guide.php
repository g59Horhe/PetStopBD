<?php
// Owner Section Installation Guide
// This file helps set up the complete Owner Section for PetStopBD

// Check if we're being run from the correct directory
if (!file_exists('config/db_connect.php')) {
    die('<h2 style="color: red;">Error: Please run this file from your PetStopBD root directory!</h2>');
}

// Include database connection
require_once 'config/db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetStopBD Owner Section Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            padding: 2rem 0;
        }
        .install-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 0 8px 8px 0;
        }
        .step-success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .step-error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .step-warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        .btn-install:hover {
            transform: translateY(-2px);
            color: white;
        }
        .progress {
            height: 10px;
            border-radius: 10px;
            overflow: hidden;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .feature-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 1rem;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 1rem 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-card">
            <div class="text-center mb-4">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-paw me-3" style="color: #667eea;"></i>
                    PetStopBD Owner Section
                </h1>
                <p class="lead">Complete Pet Management System Installation</p>
            </div>

            <?php
            $installationSteps = [];
            $allGood = true;

            // Step 1: Check Database Connection
            $installationSteps[] = [
                'title' => 'Database Connection',
                'status' => 'success',
                'message' => 'Database connection successful',
                'details' => 'Connected to MySQL database: ' . $conn->info
            ];

            // Step 2: Check if main tables exist
            $tablesExist = false;
            try {
                $result = $conn->query("SHOW TABLES LIKE 'users'");
                if ($result && $result->num_rows > 0) {
                    $tablesExist = true;
                    $installationSteps[] = [
                        'title' => 'Core Tables',
                        'status' => 'success',
                        'message' => 'Core PetStopBD tables found',
                        'details' => 'Main application tables are properly installed'
                    ];
                } else {
                    $allGood = false;
                    $installationSteps[] = [
                        'title' => 'Core Tables',
                        'status' => 'error',
                        'message' => 'Core tables missing',
                        'details' => 'Please run db_setup.php first to create the main application tables'
                    ];
                }
            } catch (Exception $e) {
                $allGood = false;
                $installationSteps[] = [
                    'title' => 'Core Tables',
                    'status' => 'error',
                    'message' => 'Database error: ' . $e->getMessage(),
                    'details' => 'Check your database connection and permissions'
                ];
            }

            // Step 3: Check Owner Section Tables
            if ($tablesExist) {
                try {
                    $ownerTables = ['pet_types', 'pet_breeds', 'pet_profiles', 'pet_photos', 'medical_records'];
                    $missingTables = [];
                    
                    foreach ($ownerTables as $table) {
                        $result = $conn->query("SHOW TABLES LIKE '$table'");
                        if (!$result || $result->num_rows == 0) {
                            $missingTables[] = $table;
                        }
                    }
                    
                    if (empty($missingTables)) {
                        $installationSteps[] = [
                            'title' => 'Owner Section Tables',
                            'status' => 'success',
                            'message' => 'All Owner Section tables exist',
                            'details' => 'Pet management tables are properly installed'
                        ];
                    } else {
                        $allGood = false;
                        $installationSteps[] = [
                            'title' => 'Owner Section Tables',
                            'status' => 'warning',
                            'message' => 'Missing tables: ' . implode(', ', $missingTables),
                            'details' => 'Need to create Owner Section database tables'
                        ];
                    }
                } catch (Exception $e) {
                    $allGood = false;
                    $installationSteps[] = [
                        'title' => 'Owner Section Tables',
                        'status' => 'error',
                        'message' => 'Error checking tables: ' . $e->getMessage(),
                        'details' => 'Database access issue'
                    ];
                }
            }

            // Step 4: Check Directories
            $directories = ['owner', 'uploads', 'uploads/pets'];
            $missingDirs = [];
            
            foreach ($directories as $dir) {
                if (!file_exists($dir)) {
                    $missingDirs[] = $dir;
                }
            }
            
            if (empty($missingDirs)) {
                $installationSteps[] = [
                    'title' => 'Directory Structure',
                    'status' => 'success',
                    'message' => 'All required directories exist',
                    'details' => 'File structure is properly set up'
                ];
            } else {
                $installationSteps[] = [
                    'title' => 'Directory Structure',
                    'status' => 'warning',
                    'message' => 'Missing directories: ' . implode(', ', $missingDirs),
                    'details' => 'These directories will be created automatically'
                ];
            }

            // Step 5: Check File Permissions
            if (file_exists('uploads')) {
                if (is_writable('uploads')) {
                    $installationSteps[] = [
                        'title' => 'File Permissions',
                        'status' => 'success',
                        'message' => 'Upload directory is writable',
                        'details' => 'Photos can be uploaded successfully'
                    ];
                } else {
                    $installationSteps[] = [
                        'title' => 'File Permissions',
                        'status' => 'warning',
                        'message' => 'Upload directory not writable',
                        'details' => 'chmod 755 uploads/ may be needed'
                    ];
                }
            }

            // Step 6: Check PHP Extensions
            $requiredExtensions = ['mysqli', 'gd', 'fileinfo'];
            $missingExtensions = [];
            
            foreach ($requiredExtensions as $ext) {
                if (!extension_loaded($ext)) {
                    $missingExtensions[] = $ext;
                }
            }
            
            if (empty($missingExtensions)) {
                $installationSteps[] = [
                    'title' => 'PHP Extensions',
                    'status' => 'success',
                    'message' => 'All required PHP extensions loaded',
                    'details' => 'mysqli, gd, and fileinfo extensions are available'
                ];
            } else {
                $allGood = false;
                $installationSteps[] = [
                    'title' => 'PHP Extensions',
                    'status' => 'error',
                    'message' => 'Missing extensions: ' . implode(', ', $missingExtensions),
                    'details' => 'Please install missing PHP extensions'
                ];
            }

            // Calculate progress
            $successSteps = count(array_filter($installationSteps, fn($step) => $step['status'] === 'success'));
            $totalSteps = count($installationSteps);
            $progress = ($successSteps / $totalSteps) * 100;
            ?>

            <!-- Progress Bar -->
            <div class="mb-4">
                <h5>Installation Progress</h5>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                </div>
                <small class="text-muted"><?php echo $successSteps; ?> of <?php echo $totalSteps; ?> checks passed</small>
            </div>

            <!-- Installation Steps -->
            <h4 class="mb-3">System Checks</h4>
            <?php foreach ($installationSteps as $step): ?>
                <div class="step <?php echo 'step-' . $step['status']; ?>">
                    <h6>
                        <?php if ($step['status'] === 'success'): ?>
                            <i class="fas fa-check-circle text-success me-2"></i>
                        <?php elseif ($step['status'] === 'warning'): ?>
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-danger me-2"></i>
                        <?php endif; ?>
                        <?php echo $step['title']; ?>
                    </h6>
                    <p class="mb-1"><strong><?php echo $step['message']; ?></strong></p>
                    <small class="text-muted"><?php echo $step['details']; ?></small>
                </div>
            <?php endforeach; ?>

            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <?php if ($progress < 100): ?>
                    <form method="post" action="owner_db_setup.php" class="d-inline">
                        <button type="submit" class="btn btn-install me-3">
                            <i class="fas fa-cog me-2"></i>Run Database Setup
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($progress >= 70): ?>
                    <a href="owner/index.php" class="btn btn-success btn-lg">
                        <i class="fas fa-rocket me-2"></i>Launch Owner Dashboard
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($progress >= 100): ?>
                <div class="alert alert-success mt-4">
                    <h5><i class="fas fa-check-circle me-2"></i>Installation Complete!</h5>
                    <p class="mb-0">Your Owner Section is ready to use. You can now manage pets, medical records, and more!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Features Overview -->
        <div class="install-card">
            <h3 class="text-center mb-4">What You Get with Owner Section</h3>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5>Pet Profiles</h5>
                    <p>Complete pet management with photos, breed info, and personal details.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h5>Medical Records</h5>
                    <p>Track vaccinations, checkups, treatments, and upcoming appointments.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h5>Photo Gallery</h5>
                    <p>Upload multiple photos per pet with beautiful gallery viewing.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Health Analytics</h5>
                    <p>Dashboard insights, statistics, and health tracking over time.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5>Appointment Tracking</h5>
                    <p>Never miss important vet visits with upcoming appointment reminders.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h5>Mobile Responsive</h5>
                    <p>Works perfectly on all devices - desktop, tablet, and mobile.</p>
                </div>
            </div>
        </div>

        <!-- Quick Start Guide -->
        <div class="install-card">
            <h3>Quick Start Guide</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>1. First Login</h5>
                    <p>Register or login to your PetStopBD account to access the Owner Section.</p>
                    
                    <h5>2. Add Your First Pet</h5>
                    <p>Click "Add New Pet" and fill in your pet's information with a photo.</p>
                    
                    <h5>3. Medical Records</h5>
                    <p>Start tracking your pet's health by adding vaccination and checkup records.</p>
                </div>
                <div class="col-md-6">
                    <h5>4. Photo Gallery</h5>
                    <p>Upload multiple photos of your pet and set a primary profile picture.</p>
                    
                    <h5>5. Dashboard</h5>
                    <p>Monitor all your pets from the dashboard with health insights and reminders.</p>
                    
                    <h5>6. Explore Features</h5>
                    <p>Try filtering, sorting, and managing multiple pets to see all features.</p>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Need Help?</h5>
                <p>Check the <code>OWNER_README.md</code> file for detailed documentation and troubleshooting guides.</p>
            </div>
        </div>

        <div class="text-center">
            <p class="text-muted">
                <i class="fas fa-paw me-2"></i>
                PetStopBD Owner Section - Your Complete Pet Management Solution
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>