<?php
// Owner Dashboard - Main page for pet management
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

// Get user's pets with type and breed information
$pets_query = "SELECT p.*, pt.name as pet_type, pt.icon as type_icon, pb.name as breed_name,
                      (SELECT photo_url FROM pet_photos pp WHERE pp.pet_id = p.id AND pp.is_primary = 1 LIMIT 1) as profile_image
               FROM pet_profiles p 
               LEFT JOIN pet_types pt ON p.pet_type_id = pt.id
               LEFT JOIN pet_breeds pb ON p.breed_id = pb.id
               WHERE p.user_id = ? AND p.is_active = 1
               ORDER BY p.created_at DESC";
$pets_stmt = $conn->prepare($pets_query);
$pets_stmt->bind_param("i", $user_id);
$pets_stmt->execute();
$pets = $pets_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total pet count
$total_pets = count($pets);

// Get upcoming medical appointments (next 30 days)
$upcoming_query = "SELECT mr.*, p.name as pet_name, p.profile_image, pt.icon as type_icon
                   FROM medical_records mr 
                   JOIN pet_profiles p ON mr.pet_id = p.id
                   JOIN pet_types pt ON p.pet_type_id = pt.id
                   WHERE p.user_id = ? AND mr.next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                   ORDER BY mr.next_due_date ASC LIMIT 5";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $user_id);
$upcoming_stmt->execute();
$upcoming_appointments = $upcoming_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get recent medical records (last 10)
$recent_query = "SELECT mr.*, p.name as pet_name, pt.icon as type_icon
                 FROM medical_records mr 
                 JOIN pet_profiles p ON mr.pet_id = p.id
                 JOIN pet_types pt ON p.pet_type_id = pt.id
                 WHERE p.user_id = ? 
                 ORDER BY mr.date_performed DESC LIMIT 5";
$recent_stmt = $conn->prepare($recent_query);
$recent_stmt->bind_param("i", $user_id);
$recent_stmt->execute();
$recent_records = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate statistics
$vaccination_count = 0;
$checkup_count = 0;
foreach ($recent_records as $record) {
    if ($record['record_type'] == 'vaccination') $vaccination_count++;
    if ($record['record_type'] == 'checkup') $checkup_count++;
}

// Include header
include '../includes/header.php';
?>

<div class="owner-dashboard">
    <!-- Dashboard Header -->
    <section class="dashboard-header py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="dashboard-title">
                        <i class="fas fa-paw me-3"></i>My Pets Dashboard
                    </h1>
                    <p class="dashboard-subtitle">Manage your pets' health, records, and information</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="add_pet.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Add New Pet
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Overview -->
    <section class="stats-overview py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-paw"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $total_pets; ?></h3>
                            <p class="stat-label">Total Pets</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon urgent">
                            <i class="fas fa-calendar-exclamation"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo count($upcoming_appointments); ?></h3>
                            <p class="stat-label">Upcoming Appointments</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $vaccination_count; ?></h3>
                            <p class="stat-label">Recent Vaccinations</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo $checkup_count; ?></h3>
                            <p class="stat-label">Recent Checkups</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="dashboard-content py-4">
        <div class="container">
            <div class="row g-4">
                <!-- My Pets Section -->
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-heart me-2"></i>My Pets
                            </h3>
                            <a href="my_pets.php" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pets)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                    <h4>No Pets Added Yet</h4>
                                    <p>Start by adding your first pet to track their health and records.</p>
                                    <a href="add_pet.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Your First Pet
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="pets-grid">
                                    <?php foreach (array_slice($pets, 0, 4) as $pet): ?>
                                        <div class="pet-card">
                                            <div class="pet-image">
                                                <?php if ($pet['profile_image']): ?>
                                                    <img src="../uploads/pets/<?php echo htmlspecialchars($pet['profile_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                                <?php else: ?>
                                                    <div class="pet-placeholder">
                                                        <i class="<?php echo htmlspecialchars($pet['type_icon']); ?>"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pet-info">
                                                <h5 class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></h5>
                                                <p class="pet-details">
                                                    <span class="pet-type"><?php echo htmlspecialchars($pet['pet_type']); ?></span>
                                                    <?php if ($pet['breed_name']): ?>
                                                        <span class="pet-breed"> • <?php echo htmlspecialchars($pet['breed_name']); ?></span>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if ($pet['date_of_birth']): ?>
                                                    <p class="pet-age">
                                                        <i class="fas fa-birthday-cake me-1"></i>
                                                        <?php 
                                                        $birth_date = new DateTime($pet['date_of_birth']);
                                                        $today = new DateTime();
                                                        $age = $birth_date->diff($today);
                                                        if ($age->y > 0) {
                                                            echo $age->y . ' years';
                                                            if ($age->m > 0) echo ', ' . $age->m . ' months';
                                                        } else {
                                                            echo $age->m . ' months';
                                                        }
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pet-actions">
                                                <a href="view_pet.php?id=<?php echo $pet['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                                <a href="edit_pet.php?id=<?php echo $pet['id']; ?>" 
                                                   class="btn btn-sm btn-primary">Edit</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (count($pets) > 4): ?>
                                    <div class="text-center mt-4">
                                        <a href="my_pets.php" class="btn btn-outline-primary">
                                            View All <?php echo count($pets); ?> Pets
                                            <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <!-- Upcoming Appointments -->
                    <div class="dashboard-card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-calendar-alt me-2"></i>Upcoming Appointments
                            </h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($upcoming_appointments)): ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                    <p>No upcoming appointments</p>
                                </div>
                            <?php else: ?>
                                <div class="appointments-list">
                                    <?php foreach ($upcoming_appointments as $appointment): ?>
                                        <div class="appointment-item">
                                            <div class="appointment-icon">
                                                <i class="<?php echo htmlspecialchars($appointment['type_icon']); ?>"></i>
                                            </div>
                                            <div class="appointment-details">
                                                <h6 class="appointment-title"><?php echo htmlspecialchars($appointment['title']); ?></h6>
                                                <p class="appointment-pet"><?php echo htmlspecialchars($appointment['pet_name']); ?></p>
                                                <p class="appointment-date">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M j, Y', strtotime($appointment['next_due_date'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Medical Records -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-file-medical me-2"></i>Recent Records
                            </h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_records)): ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-file-medical fa-2x mb-2"></i>
                                    <p>No medical records yet</p>
                                </div>
                            <?php else: ?>
                                <div class="records-list">
                                    <?php foreach ($recent_records as $record): ?>
                                        <div class="record-item">
                                            <div class="record-icon <?php echo $record['record_type']; ?>">
                                                <?php
                                                $icon = 'fas fa-file-medical';
                                                switch($record['record_type']) {
                                                    case 'vaccination': $icon = 'fas fa-syringe'; break;
                                                    case 'checkup': $icon = 'fas fa-stethoscope'; break;
                                                    case 'treatment': $icon = 'fas fa-pills'; break;
                                                    case 'surgery': $icon = 'fas fa-cut'; break;
                                                }
                                                ?>
                                                <i class="<?php echo $icon; ?>"></i>
                                            </div>
                                            <div class="record-details">
                                                <h6 class="record-title"><?php echo htmlspecialchars($record['title']); ?></h6>
                                                <p class="record-pet"><?php echo htmlspecialchars($record['pet_name']); ?></p>
                                                <p class="record-date"><?php echo date('M j, Y', strtotime($record['date_performed'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Owner Dashboard Styles */
.owner-dashboard {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    min-height: 100vh;
}

.dashboard-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.dashboard-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.dashboard-subtitle {
    color: #718096;
    margin: 0;
    font-size: 1.1rem;
}

/* Stats Cards */
.stats-overview {
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.urgent {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-icon.info {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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

/* Dashboard Cards */
.dashboard-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    overflow: hidden;
}

.card-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: between;
    align-items: center;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    flex: 1;
}

.card-body {
    padding: 2rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
}

.empty-state h4 {
    color: #2d3748;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #718096;
    margin-bottom: 2rem;
}

/* Pets Grid */
.pets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.pet-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.pet-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.pet-image {
    text-align: center;
    margin-bottom: 1rem;
}

.pet-image img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.pet-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 2rem;
}

.pet-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    text-align: center;
}

.pet-details {
    text-align: center;
    color: #718096;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.pet-age {
    text-align: center;
    color: #718096;
    font-size: 0.85rem;
    margin-bottom: 1rem;
}

.pet-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

/* Appointments and Records Lists */
.appointments-list,
.records-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.appointment-item,
.record-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.appointment-icon,
.record-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    flex-shrink: 0;
}

.record-icon.vaccination {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.record-icon.checkup {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.record-icon.treatment {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.appointment-details,
.record-details {
    flex: 1;
}

.appointment-title,
.record-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.appointment-pet,
.record-pet {
    font-size: 0.85rem;
    color: #718096;
    margin-bottom: 0.25rem;
}

.appointment-date,
.record-date {
    font-size: 0.8rem;
    color: #a0aec0;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-title {
        font-size: 1.5rem;
    }
    
    .pets-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1.5rem;
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .card-header {
        padding: 1rem 1.5rem;
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .card-body {
        padding: 1.5rem;
    }
}
</style>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>