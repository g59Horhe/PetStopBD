<?php
// Pet Medical Records - Comprehensive medical management for pets
require_once '../config/db_connect.php';
require_once '../includes/auth_functions.php';

// Start session and check authentication
session_start();

// Redirect if not logged in
if (!is_logged_in()) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get pet ID from URL
$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pet_id <= 0) {
    header('Location: my_pets.php');
    exit();
}

// Verify pet ownership
$pet_query = "SELECT p.*, pt.name as pet_type, pt.icon as type_icon 
              FROM pet_profiles p 
              LEFT JOIN pet_types pt ON p.pet_type_id = pt.id
              WHERE p.id = ? AND p.user_id = ? AND p.is_active = 1";
$pet_stmt = $conn->prepare($pet_query);
$pet_stmt->bind_param("ii", $pet_id, $user_id);
$pet_stmt->execute();
$pet_result = $pet_stmt->get_result();

if ($pet_result->num_rows === 0) {
    header('Location: my_pets.php');
    exit();
}

$pet = $pet_result->fetch_assoc();

// Handle form submissions
$success_msg = $error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Handle adding new medical record
    if (isset($_POST['add_record'])) {
        $record_type = trim($_POST['record_type']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $date_performed = $_POST['date_performed'];
        $veterinarian_name = trim($_POST['veterinarian_name']);
        $clinic_name = trim($_POST['clinic_name']);
        $cost = !empty($_POST['cost']) ? (float)$_POST['cost'] : null;
        $next_due_date = !empty($_POST['next_due_date']) ? $_POST['next_due_date'] : null;
        $notes = trim($_POST['notes']);
        
        if (!empty($title) && !empty($record_type) && !empty($date_performed)) {
            $insert_query = "INSERT INTO medical_records (pet_id, record_type, title, description, date_performed, 
                            veterinarian_name, clinic_name, cost, next_due_date, notes) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issssssdss", $pet_id, $record_type, $title, $description, $date_performed, 
                             $veterinarian_name, $clinic_name, $cost, $next_due_date, $notes);
            
            if ($stmt->execute()) {
                $success_msg = "Medical record added successfully!";
            } else {
                $error_msg = "Failed to add medical record. Please try again.";
            }
        } else {
            $error_msg = "Please fill in all required fields.";
        }
    }
    
    // Handle deleting medical record
    if (isset($_POST['delete_record'])) {
        $record_id = (int)$_POST['delete_record'];
        
        $delete_query = "DELETE FROM medical_records WHERE id = ? AND pet_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("ii", $record_id, $pet_id);
        
        if ($delete_stmt->execute()) {
            $success_msg = "Medical record deleted successfully!";
        } else {
            $error_msg = "Failed to delete medical record.";
        }
    }
}

// Get filter parameters
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// Build query with filters
$where_conditions = ["pet_id = ?"];
$params = [$pet_id];
$param_types = "i";

if (!empty($filter_type)) {
    $where_conditions[] = "record_type = ?";
    $params[] = $filter_type;
    $param_types .= "s";
}

// Determine sort order
$order_by = "date_performed DESC";
switch ($sort_by) {
    case 'date_asc':
        $order_by = "date_performed ASC";
        break;
    case 'date_desc':
        $order_by = "date_performed DESC";
        break;
    case 'type':
        $order_by = "record_type ASC, date_performed DESC";
        break;
    case 'title':
        $order_by = "title ASC";
        break;
}

// Get medical records
$records_query = "SELECT * FROM medical_records WHERE " . implode(" AND ", $where_conditions) . " ORDER BY $order_by";
$records_stmt = $conn->prepare($records_query);
if (!empty($params)) {
    $records_stmt->bind_param($param_types, ...$params);
}
$records_stmt->execute();
$medical_records = $records_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get upcoming appointments (next 90 days)
$upcoming_query = "SELECT * FROM medical_records WHERE pet_id = ? AND next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) ORDER BY next_due_date ASC";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $pet_id);
$upcoming_stmt->execute();
$upcoming_appointments = $upcoming_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get record type counts for statistics
$stats_query = "SELECT record_type, COUNT(*) as count FROM medical_records WHERE pet_id = ? GROUP BY record_type";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $pet_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stats = [];
foreach ($stats_result as $stat) {
    $stats[$stat['record_type']] = $stat['count'];
}

// Include header
include '../includes/header.php';
?>

<div class="medical-records-page">
    <div class="container py-5">
        
        <!-- Back Navigation -->
        <div class="back-nav mb-4">
            <a href="view_pet.php?id=<?php echo $pet_id; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to <?php echo htmlspecialchars($pet['name']); ?>
            </a>
        </div>
        
        <!-- Page Header -->
        <div class="page-header mb-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">
                        <i class="<?php echo htmlspecialchars($pet['type_icon']); ?> me-3"></i>
                        <?php echo htmlspecialchars($pet['name']); ?>'s Medical Records
                    </h1>
                    <p class="page-subtitle">
                        Complete health history and upcoming appointments
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                        <i class="fas fa-plus me-2"></i>Add Record
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-modern" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-modern" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="stats-section mb-5">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo count($medical_records); ?></h3>
                            <p class="stat-label">Total Records</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon vaccination">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $stats['vaccination'] ?? 0; ?></h3>
                            <p class="stat-label">Vaccinations</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon checkup">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $stats['checkup'] ?? 0; ?></h3>
                            <p class="stat-label">Checkups</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon upcoming">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo count($upcoming_appointments); ?></h3>
                            <p class="stat-label">Upcoming</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                
                <!-- Filters and Sort -->
                <div class="filters-section mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Filter by Type:</label>
                            <select class="form-select" onchange="filterByType(this.value)">
                                <option value="">All Types</option>
                                <option value="vaccination" <?php echo ($filter_type == 'vaccination') ? 'selected' : ''; ?>>Vaccinations</option>
                                <option value="checkup" <?php echo ($filter_type == 'checkup') ? 'selected' : ''; ?>>Checkups</option>
                                <option value="treatment" <?php echo ($filter_type == 'treatment') ? 'selected' : ''; ?>>Treatments</option>
                                <option value="surgery" <?php echo ($filter_type == 'surgery') ? 'selected' : ''; ?>>Surgeries</option>
                                <option value="medication" <?php echo ($filter_type == 'medication') ? 'selected' : ''; ?>>Medications</option>
                                <option value="other" <?php echo ($filter_type == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Sort by:</label>
                            <select class="form-select" onchange="sortBy(this.value)">
                                <option value="date_desc" <?php echo ($sort_by == 'date_desc') ? 'selected' : ''; ?>>Date (Newest First)</option>
                                <option value="date_asc" <?php echo ($sort_by == 'date_asc') ? 'selected' : ''; ?>>Date (Oldest First)</option>
                                <option value="type" <?php echo ($sort_by == 'type') ? 'selected' : ''; ?>>Type</option>
                                <option value="title" <?php echo ($sort_by == 'title') ? 'selected' : ''; ?>>Title (A-Z)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Medical Records List -->
                <div class="records-section">
                    <?php if (empty($medical_records)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <?php if (!empty($filter_type)): ?>
                                <h3>No <?php echo ucfirst($filter_type); ?> records found</h3>
                                <p>Try removing the filter or add a new record.</p>
                                <div class="empty-actions">
                                    <button class="btn btn-outline-primary me-2" onclick="clearFilters()">Clear Filter</button>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">Add Record</button>
                                </div>
                            <?php else: ?>
                                <h3>No medical records yet</h3>
                                <p>Start tracking your pet's health by adding the first medical record.</p>
                                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                                    <i class="fas fa-plus me-2"></i>Add First Record
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="records-timeline">
                            <?php foreach ($medical_records as $record): ?>
                                <div class="record-item" data-aos="fade-up">
                                    <div class="record-timeline-marker <?php echo $record['record_type']; ?>">
                                        <?php
                                        $icon = 'fas fa-file-medical';
                                        switch($record['record_type']) {
                                            case 'vaccination': $icon = 'fas fa-syringe'; break;
                                            case 'checkup': $icon = 'fas fa-stethoscope'; break;
                                            case 'treatment': $icon = 'fas fa-pills'; break;
                                            case 'surgery': $icon = 'fas fa-cut'; break;
                                            case 'medication': $icon = 'fas fa-prescription-bottle'; break;
                                        }
                                        ?>
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                    
                                    <div class="record-content">
                                        <div class="record-header">
                                            <div class="record-title-section">
                                                <h4 class="record-title"><?php echo htmlspecialchars($record['title']); ?></h4>
                                                <div class="record-meta">
                                                    <span class="record-date">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo date('M j, Y', strtotime($record['date_performed'])); ?>
                                                    </span>
                                                    <span class="record-type-badge <?php echo $record['record_type']; ?>">
                                                        <?php echo ucfirst($record['record_type']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="record-actions">
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="editRecord(<?php echo $record['id']; ?>)">
                                                            <i class="fas fa-edit me-2"></i>Edit
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record?')">
                                                                <input type="hidden" name="delete_record" value="<?php echo $record['id']; ?>">
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-2"></i>Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if ($record['description']): ?>
                                            <div class="record-description">
                                                <p><?php echo nl2br(htmlspecialchars($record['description'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="record-details">
                                            <div class="row g-3">
                                                <?php if ($record['veterinarian_name']): ?>
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <i class="fas fa-user-md me-2"></i>
                                                            <span class="detail-label">Veterinarian:</span>
                                                            <span class="detail-value"><?php echo htmlspecialchars($record['veterinarian_name']); ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($record['clinic_name']): ?>
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <i class="fas fa-hospital me-2"></i>
                                                            <span class="detail-label">Clinic:</span>
                                                            <span class="detail-value"><?php echo htmlspecialchars($record['clinic_name']); ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($record['cost']): ?>
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <i class="fas fa-dollar-sign me-2"></i>
                                                            <span class="detail-label">Cost:</span>
                                                            <span class="detail-value">৳<?php echo number_format($record['cost'], 2); ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($record['next_due_date']): ?>
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <i class="fas fa-calendar-check me-2"></i>
                                                            <span class="detail-label">Next Due:</span>
                                                            <span class="detail-value <?php echo (strtotime($record['next_due_date']) < time()) ? 'overdue' : ''; ?>">
                                                                <?php echo date('M j, Y', strtotime($record['next_due_date'])); ?>
                                                                <?php if (strtotime($record['next_due_date']) < time()): ?>
                                                                    <small class="text-danger">(Overdue)</small>
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($record['notes']): ?>
                                            <div class="record-notes">
                                                <h6>Notes:</h6>
                                                <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                
                <!-- Upcoming Appointments -->
                <div class="sidebar-card mb-4">
                    <h4 class="sidebar-title">
                        <i class="fas fa-calendar-alt me-2"></i>Upcoming Appointments
                    </h4>
                    
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="empty-sidebar">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <p>No upcoming appointments</p>
                            <small class="text-muted">Records with future due dates will appear here</small>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <div class="appointment-item">
                                    <div class="appointment-date">
                                        <div class="date-day"><?php echo date('j', strtotime($appointment['next_due_date'])); ?></div>
                                        <div class="date-month"><?php echo date('M', strtotime($appointment['next_due_date'])); ?></div>
                                    </div>
                                    <div class="appointment-details">
                                        <h6><?php echo htmlspecialchars($appointment['title']); ?></h6>
                                        <p class="appointment-type"><?php echo ucfirst($appointment['record_type']); ?></p>
                                        <small class="days-until">
                                            <?php 
                                            $days = floor((strtotime($appointment['next_due_date']) - time()) / (60 * 60 * 24));
                                            if ($days == 0) echo "Today";
                                            elseif ($days == 1) echo "Tomorrow";
                                            elseif ($days < 0) echo abs($days) . " days overdue";
                                            else echo "In " . $days . " days";
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats -->
                <div class="sidebar-card">
                    <h4 class="sidebar-title">
                        <i class="fas fa-chart-pie me-2"></i>Health Summary
                    </h4>
                    
                    <div class="quick-stats">
                        <?php if (!empty($medical_records)): ?>
                            <div class="stat-row">
                                <span class="stat-label">Last Visit:</span>
                                <span class="stat-value"><?php echo date('M j, Y', strtotime($medical_records[0]['date_performed'])); ?></span>
                            </div>
                            
                            <?php 
                            $last_vaccination = null;
                            foreach ($medical_records as $record) {
                                if ($record['record_type'] == 'vaccination') {
                                    $last_vaccination = $record['date_performed'];
                                    break;
                                }
                            }
                            ?>
                            
                            <?php if ($last_vaccination): ?>
                                <div class="stat-row">
                                    <span class="stat-label">Last Vaccination:</span>
                                    <span class="stat-value"><?php echo date('M j, Y', strtotime($last_vaccination)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="stat-row">
                                <span class="stat-label">Total Visits:</span>
                                <span class="stat-value"><?php echo count($medical_records); ?></span>
                            </div>
                            
                            <?php 
                            $total_cost = 0;
                            foreach ($medical_records as $record) {
                                $total_cost += $record['cost'];
                            }
                            ?>
                            
                            <?php if ($total_cost > 0): ?>
                                <div class="stat-row">
                                    <span class="stat-label">Total Spent:</span>
                                    <span class="stat-value">৳<?php echo number_format($total_cost, 2); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <p>No data available yet</p>
                                <small>Add medical records to see statistics</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Medical Record Modal -->
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRecordModalLabel">
                    <i class="fas fa-plus me-2"></i>Add Medical Record for <?php echo htmlspecialchars($pet['name']); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="add_record" value="1">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="record_type" class="form-label">Record Type *</label>
                            <select name="record_type" id="record_type" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="checkup">Checkup</option>
                                <option value="treatment">Treatment</option>
                                <option value="surgery">Surgery</option>
                                <option value="medication">Medication</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="date_performed" class="form-label">Date Performed *</label>
                            <input type="date" name="date_performed" id="date_performed" class="form-control" required max="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title/Procedure *</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g., Annual Vaccination, Rabies Shot" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Detailed description of the procedure or treatment"></textarea>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="veterinarian_name" class="form-label">Veterinarian Name</label>
                            <input type="text" name="veterinarian_name" id="veterinarian_name" class="form-control" placeholder="Dr. Smith">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="clinic_name" class="form-label">Clinic/Hospital Name</label>
                            <input type="text" name="clinic_name" id="clinic_name" class="form-control" placeholder="ABC Veterinary Clinic">
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cost" class="form-label">Cost (৳)</label>
                            <input type="number" name="cost" id="cost" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="next_due_date" class="form-label">Next Due Date</label>
                            <input type="date" name="next_due_date" id="next_due_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Any additional notes or observations"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Add Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add AOS for animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
/* Medical Records Page Styles */
.medical-records-page {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    min-height: 100vh;
}

.back-nav .btn {
    border-radius: 10px;
    font-weight: 600;
}

.page-header {
    text-align: center;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #718096;
    font-size: 1.1rem;
    margin: 0;
}

.alert-modern {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
}

/* Stats Cards */
.stats-section {
    background: transparent;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.stat-icon.total {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.vaccination {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-icon.checkup {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-icon.upcoming {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #2d3748;
}

.stat-label {
    color: #718096;
    margin: 0;
    font-size: 0.9rem;
}

/* Filters */
.filters-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.form-select {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
}

.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
}

/* Records Section */
.records-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2.5rem;
    color: white;
}

.empty-state h3 {
    color: #2d3748;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #718096;
    margin-bottom: 2rem;
}

.empty-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Timeline */
.records-timeline {
    position: relative;
    padding-left: 2rem;
}

.records-timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #667eea, #f8f9ff);
}

.record-item {
    position: relative;
    margin-bottom: 2rem;
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.record-item:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.record-timeline-marker {
    position: absolute;
    left: -2.75rem;
    top: 1.5rem;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.record-timeline-marker.vaccination {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.record-timeline-marker.checkup {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.record-timeline-marker.treatment {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.record-timeline-marker.surgery {
    background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
}

.record-timeline-marker.medication {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.record-timeline-marker.other {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.record-content {
    padding: 2rem;
}

.record-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.record-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.record-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.record-date {
    color: #718096;
    font-size: 0.9rem;
}

.record-type-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: capitalize;
}

.record-type-badge.vaccination {
    background: rgba(79, 172, 254, 0.1);
    color: #0369a1;
}

.record-type-badge.checkup {
    background: rgba(67, 233, 123, 0.1);
    color: #047857;
}

.record-type-badge.treatment {
    background: rgba(240, 147, 251, 0.1);
    color: #c2410c;
}

.record-type-badge.surgery {
    background: rgba(255, 234, 167, 0.1);
    color: #d97706;
}

.record-type-badge.medication {
    background: rgba(168, 237, 234, 0.1);
    color: #0891b2;
}

.record-type-badge.other {
    background: rgba(102, 126, 234, 0.1);
    color: #4338ca;
}

.record-description {
    margin-bottom: 1.5rem;
}

.record-description p {
    color: #4a5568;
    line-height: 1.6;
    margin: 0;
}

.record-details {
    margin-bottom: 1.5rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.detail-item i {
    color: #667eea;
    width: 16px;
}

.detail-label {
    font-weight: 600;
    color: #2d3748;
    min-width: 80px;
}

.detail-value {
    color: #718096;
}

.detail-value.overdue {
    color: #e53e3e;
    font-weight: 600;
}

.record-notes {
    background: rgba(102, 126, 234, 0.05);
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.record-notes h6 {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.record-notes p {
    color: #4a5568;
    margin: 0;
    line-height: 1.6;
}

/* Sidebar */
.sidebar-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.sidebar-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.empty-sidebar {
    text-align: center;
    padding: 2rem 1rem;
    color: #718096;
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.appointment-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.appointment-date {
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem;
    border-radius: 10px;
    min-width: 60px;
    flex-shrink: 0;
}

.date-day {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.date-month {
    font-size: 0.8rem;
    text-transform: uppercase;
    margin-top: 0.25rem;
}

.appointment-details h6 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.appointment-type {
    font-size: 0.8rem;
    color: #718096;
    margin-bottom: 0.25rem;
}

.days-until {
    font-size: 0.75rem;
    color: #667eea;
    font-weight: 600;
}

.quick-stats {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-row .stat-label {
    font-weight: 500;
    color: #4a5568;
    font-size: 0.9rem;
}

.stat-row .stat-value {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
}

/* Modal Styles */
.modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-bottom: 1px solid #f1f5f9;
    padding: 2rem 2rem 1rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    border-top: 1px solid #f1f5f9;
    padding: 1rem 2rem 2rem;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header .row {
        text-align: center;
    }
    
    .page-header .col-md-4 {
        margin-top: 1rem;
    }
    
    .stat-card {
        padding: 1.5rem;
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .filters-section {
        padding: 1.5rem;
    }
    
    .records-timeline {
        padding-left: 0;
    }
    
    .records-timeline::before {
        display: none;
    }
    
    .record-timeline-marker {
        position: static;
        margin: 0 auto 1rem;
    }
    
    .record-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .record-meta {
        justify-content: space-between;
        width: 100%;
    }
    
    .appointment-item {
        flex-direction: column;
        text-align: center;
    }
    
    .appointment-date {
        width: 100%;
        max-width: 120px;
    }
}
</style>

<script>
// Initialize AOS
AOS.init({
    duration: 600,
    once: true,
    offset: 100
});

// Filter and sort functions
function filterByType(type) {
    const url = new URL(window.location);
    if (type) {
        url.searchParams.set('type', type);
    } else {
        url.searchParams.delete('type');
    }
    window.location.href = url.toString();
}

function sortBy(sort) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url.toString();
}

function clearFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('type');
    window.location.href = url.toString();
}

// Edit record function (placeholder)
function editRecord(recordId) {
    // This would open an edit modal
    alert('Edit functionality would be implemented here for record ID: ' + recordId);
}

// Set today's date as default for new records
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date_performed');
    if (dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
});

// Auto-fill next due date based on record type
document.getElementById('record_type').addEventListener('change', function() {
    const nextDueInput = document.getElementById('next_due_date');
    const today = new Date();
    let nextDue = null;
    
    switch(this.value) {
        case 'vaccination':
            // Annual vaccination
            nextDue = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());
            break;
        case 'checkup':
            // Annual checkup
            nextDue = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());
            break;
        default:
            nextDue = null;
    }
    
    if (nextDue) {
        nextDueInput.value = nextDue.toISOString().split('T')[0];
    } else {
        nextDueInput.value = '';
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>