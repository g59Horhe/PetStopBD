<?php
// View Pet Details - Display detailed information about a specific pet
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

// Get pet details with type and breed information
$pet_query = "SELECT p.*, pt.name as pet_type, pt.icon as type_icon, pb.name as breed_name, 
                     pb.size_category, pb.life_expectancy_min, pb.life_expectancy_max
              FROM pet_profiles p 
              LEFT JOIN pet_types pt ON p.pet_type_id = pt.id
              LEFT JOIN pet_breeds pb ON p.breed_id = pb.id
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

// Get pet photos
$photos_query = "SELECT * FROM pet_photos WHERE pet_id = ? ORDER BY is_primary DESC, created_at ASC";
$photos_stmt = $conn->prepare($photos_query);
$photos_stmt->bind_param("i", $pet_id);
$photos_stmt->execute();
$photos = $photos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get recent medical records (last 5)
$medical_query = "SELECT * FROM medical_records WHERE pet_id = ? ORDER BY date_performed DESC LIMIT 5";
$medical_stmt = $conn->prepare($medical_query);
$medical_stmt->bind_param("i", $pet_id);
$medical_stmt->execute();
$medical_records = $medical_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get upcoming medical appointments
$upcoming_query = "SELECT * FROM medical_records WHERE pet_id = ? AND next_due_date >= CURDATE() ORDER BY next_due_date ASC LIMIT 3";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $pet_id);
$upcoming_stmt->execute();
$upcoming_appointments = $upcoming_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate pet age
$age_string = '';
if ($pet['date_of_birth']) {
    $birth_date = new DateTime($pet['date_of_birth']);
    $today = new DateTime();
    $age = $birth_date->diff($today);
    
    if ($age->y > 0) {
        $age_string = $age->y . ' year' . ($age->y > 1 ? 's' : '');
        if ($age->m > 0) {
            $age_string .= ', ' . $age->m . ' month' . ($age->m > 1 ? 's' : '');
        }
    } else {
        $age_string = $age->m . ' month' . ($age->m > 1 ? 's' : '');
    }
    $age_string .= ' old';
}

// Include header
include '../includes/header.php';
?>

<div class="view-pet-page">
    <div class="container py-5">
        
        <!-- Back Navigation -->
        <div class="back-nav mb-4">
            <a href="my_pets.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to My Pets
            </a>
        </div>

        <!-- Pet Header Section -->
        <div class="pet-header-section mb-5">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <div class="pet-main-image">
                        <?php if (!empty($photos)): ?>
                            <img src="../uploads/pets/<?php echo htmlspecialchars($photos[0]['photo_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($pet['name']); ?>"
                                 class="main-pet-photo">
                        <?php else: ?>
                            <div class="pet-placeholder-large">
                                <i class="<?php echo htmlspecialchars($pet['type_icon']); ?>"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Pet Type Badge -->
                        <div class="pet-type-badge">
                            <i class="<?php echo htmlspecialchars($pet['type_icon']); ?> me-1"></i>
                            <?php echo htmlspecialchars($pet['pet_type']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="pet-main-info">
                        <h1 class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></h1>
                        
                        <div class="pet-basic-info">
                            <?php if ($pet['breed_name']): ?>
                                <div class="info-item">
                                    <i class="fas fa-dna"></i>
                                    <span class="label">Breed:</span>
                                    <span class="value"><?php echo htmlspecialchars($pet['breed_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($pet['gender'] !== 'unknown'): ?>
                                <div class="info-item">
                                    <i class="fas fa-<?php echo ($pet['gender'] == 'male') ? 'mars' : 'venus'; ?>"></i>
                                    <span class="label">Gender:</span>
                                    <span class="value"><?php echo ucfirst($pet['gender']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($age_string): ?>
                                <div class="info-item">
                                    <i class="fas fa-birthday-cake"></i>
                                    <span class="label">Age:</span>
                                    <span class="value"><?php echo $age_string; ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($pet['weight']): ?>
                                <div class="info-item">
                                    <i class="fas fa-weight"></i>
                                    <span class="label">Weight:</span>
                                    <span class="value"><?php echo $pet['weight']; ?> kg</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($pet['color']): ?>
                                <div class="info-item">
                                    <i class="fas fa-palette"></i>
                                    <span class="label">Color:</span>
                                    <span class="value"><?php echo htmlspecialchars($pet['color']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($pet['microchip_number']): ?>
                                <div class="info-item">
                                    <i class="fas fa-microchip"></i>
                                    <span class="label">Microchip:</span>
                                    <span class="value"><?php echo htmlspecialchars($pet['microchip_number']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pet-actions mt-4">
                            <a href="edit_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Pet
                            </a>
                            <a href="pet_medical.php?id=<?php echo $pet['id']; ?>" class="btn btn-success">
                                <i class="fas fa-file-medical me-2"></i>Medical Records
                            </a>
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#photoGalleryModal">
                                <i class="fas fa-camera me-2"></i>Photos (<?php echo count($photos); ?>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pet Description -->
        <?php if ($pet['description']): ?>
        <div class="pet-description-section mb-5">
            <div class="info-card">
                <h3 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>About <?php echo htmlspecialchars($pet['name']); ?>
                </h3>
                <p class="description-text"><?php echo nl2br(htmlspecialchars($pet['description'])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Content Grid -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                
                <!-- Recent Medical Records -->
                <div class="info-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-medical me-2"></i>Recent Medical Records
                        </h3>
                        <a href="pet_medical.php?id=<?php echo $pet['id']; ?>" class="btn btn-outline-primary btn-sm">
                            View All Records
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($medical_records)): ?>
                            <div class="empty-state-small">
                                <i class="fas fa-file-medical fa-2x mb-2"></i>
                                <p>No medical records yet</p>
                                <a href="pet_medical.php?id=<?php echo $pet['id']; ?>" class="btn btn-primary btn-sm">
                                    Add First Record
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="medical-records-list">
                                <?php foreach ($medical_records as $record): ?>
                                    <div class="medical-record-item">
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
                                            <p class="record-date"><?php echo date('M j, Y', strtotime($record['date_performed'])); ?></p>
                                            <?php if ($record['description']): ?>
                                                <p class="record-description"><?php echo htmlspecialchars(substr($record['description'], 0, 100)); ?>...</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="record-type-badge">
                                            <?php echo ucfirst($record['record_type']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pet Photos Thumbnail Grid -->
                <?php if (!empty($photos)): ?>
                <div class="info-card">
                    <h3 class="card-title">
                        <i class="fas fa-camera me-2"></i>Photo Gallery
                    </h3>
                    <div class="photos-grid">
                        <?php foreach (array_slice($photos, 0, 6) as $photo): ?>
                            <div class="photo-thumbnail" data-bs-toggle="modal" data-bs-target="#photoGalleryModal">
                                <img src="../uploads/pets/<?php echo htmlspecialchars($photo['photo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                <?php if ($photo['is_primary']): ?>
                                    <div class="primary-badge">
                                        <i class="fas fa-star"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($photos) > 6): ?>
                            <div class="photo-more" data-bs-toggle="modal" data-bs-target="#photoGalleryModal">
                                <span>+<?php echo count($photos) - 6; ?> more</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                
                <!-- Upcoming Appointments -->
                <div class="info-card mb-4">
                    <h4 class="card-title">
                        <i class="fas fa-calendar-alt me-2"></i>Upcoming Appointments
                    </h4>
                    
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <p>No upcoming appointments</p>
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
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pet Stats -->
                <div class="info-card">
                    <h4 class="card-title">
                        <i class="fas fa-chart-line me-2"></i>Pet Stats
                    </h4>
                    
                    <div class="stats-list">
                        <div class="stat-item">
                            <span class="stat-label">Total Medical Records:</span>
                            <span class="stat-value"><?php echo count($medical_records); ?></span>
                        </div>
                        
                        <div class="stat-item">
                            <span class="stat-label">Photos:</span>
                            <span class="stat-value"><?php echo count($photos); ?></span>
                        </div>
                        
                        <div class="stat-item">
                            <span class="stat-label">Member Since:</span>
                            <span class="stat-value"><?php echo date('M Y', strtotime($pet['created_at'])); ?></span>
                        </div>
                        
                        <?php if ($pet['life_expectancy_min'] && $pet['life_expectancy_max']): ?>
                        <div class="stat-item">
                            <span class="stat-label">Life Expectancy:</span>
                            <span class="stat-value"><?php echo $pet['life_expectancy_min']; ?>-<?php echo $pet['life_expectancy_max']; ?> years</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Gallery Modal -->
<div class="modal fade" id="photoGalleryModal" tabindex="-1" aria-labelledby="photoGalleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoGalleryModalLabel">
                    <i class="fas fa-camera me-2"></i><?php echo htmlspecialchars($pet['name']); ?>'s Photos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($photos)): ?>
                    <div class="gallery-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="gallery-item">
                                <img src="../uploads/pets/<?php echo htmlspecialchars($photo['photo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                <?php if ($photo['is_primary']): ?>
                                    <div class="primary-badge">
                                        <i class="fas fa-star"></i> Primary
                                    </div>
                                <?php endif; ?>
                                <?php if ($photo['caption']): ?>
                                    <div class="photo-caption">
                                        <?php echo htmlspecialchars($photo['caption']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                        <p>No photos uploaded yet</p>
                        <a href="edit_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-primary">
                            Add Photos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* View Pet Page Styles */
.view-pet-page {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    min-height: 100vh;
}

.back-nav .btn {
    border-radius: 10px;
    font-weight: 600;
}

/* Pet Header Section */
.pet-header-section {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
    position: relative;
}

.pet-main-image {
    position: relative;
    display: inline-block;
}

.main-pet-photo {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    border: 6px solid white;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.pet-placeholder-large {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4rem;
    border: 6px solid white;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    margin: 0 auto;
}

.pet-type-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.pet-name {
    font-size: 3rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1.5rem;
}

.pet-basic-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.1rem;
}

.info-item i {
    color: #667eea;
    width: 20px;
    text-align: center;
}

.info-item .label {
    font-weight: 600;
    color: #2d3748;
    min-width: 80px;
}

.info-item .value {
    color: #718096;
}

.pet-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Info Cards */
.info-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.card-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
    flex: 1;
}

.description-text {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #4a5568;
    margin: 0;
}

/* Medical Records */
.medical-records-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.medical-record-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    position: relative;
}

.record-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
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

.record-icon.surgery {
    background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
}

.record-details {
    flex: 1;
}

.record-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.record-date {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 0.5rem;
}

.record-description {
    font-size: 0.85rem;
    color: #718096;
    margin: 0;
}

.record-type-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Photos Grid */
.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
}

.photo-thumbnail {
    position: relative;
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.photo-thumbnail:hover {
    transform: scale(1.05);
}

.photo-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.primary-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
    color: #d63031;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
}

.photo-more {
    aspect-ratio: 1;
    background: #f8fafc;
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #718096;
    font-weight: 600;
    transition: all 0.3s ease;
}

.photo-more:hover {
    background: #f1f5f9;
    border-color: #cbd5e0;
}

/* Appointments */
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
    margin: 0;
}

/* Stats */
.stats-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-weight: 500;
    color: #4a5568;
}

.stat-value {
    font-weight: 600;
    color: #2d3748;
}

/* Empty States */
.empty-state-small {
    text-align: center;
    padding: 2rem 1rem;
    color: #718096;
}

/* Gallery Modal */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.gallery-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
    padding: 1rem;
    font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .pet-name {
        font-size: 2rem;
    }
    
    .main-pet-photo,
    .pet-placeholder-large {
        width: 150px;
        height: 150px;
    }
    
    .pet-actions {
        justify-content: center;
    }
    
    .pet-actions .btn {
        flex: 1;
        min-width: 0;
    }
    
    .photos-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .medical-record-item {
        flex-direction: column;
        text-align: center;
    }
    
    .record-type-badge {
        position: relative;
        top: auto;
        right: auto;
        margin-top: 0.5rem;
    }
}
</style>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>