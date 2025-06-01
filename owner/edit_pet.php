<?php
// Edit Pet Form - Update existing pet profile
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

// Get existing pet data
$pet_query = "SELECT * FROM pet_profiles WHERE id = ? AND user_id = ? AND is_active = 1";
$pet_stmt = $conn->prepare($pet_query);
$pet_stmt->bind_param("ii", $pet_id, $user_id);
$pet_stmt->execute();
$pet_result = $pet_stmt->get_result();

if ($pet_result->num_rows === 0) {
    header('Location: my_pets.php');
    exit();
}

$pet = $pet_result->fetch_assoc();

// Initialize variables with existing data
$name = $pet['name'];
$pet_type_id = $pet['pet_type_id'];
$breed_id = $pet['breed_id'];
$gender = $pet['gender'];
$date_of_birth = $pet['date_of_birth'];
$weight = $pet['weight'];
$color = $pet['color'];
$microchip_number = $pet['microchip_number'];
$description = $pet['description'];

$name_err = $pet_type_err = $success_msg = $error_msg = "";

// Get pet types for dropdown
$types_query = "SELECT * FROM pet_types ORDER BY name";
$types_result = $conn->query($types_query);
$pet_types = $types_result->fetch_all(MYSQLI_ASSOC);

// Get breeds for current pet type
$breeds = [];
if ($pet_type_id) {
    $breeds_query = "SELECT * FROM pet_breeds WHERE pet_type_id = ? ORDER BY name";
    $breeds_stmt = $conn->prepare($breeds_query);
    $breeds_stmt->bind_param("i", $pet_type_id);
    $breeds_stmt->execute();
    $breeds = $breeds_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get current pet photos
$photos_query = "SELECT * FROM pet_photos WHERE pet_id = ? ORDER BY is_primary DESC, created_at ASC";
$photos_stmt = $conn->prepare($photos_query);
$photos_stmt->bind_param("i", $pet_id);
$photos_stmt->execute();
$photos = $photos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Handle photo deletion
    if (isset($_POST['delete_photo'])) {
        $photo_id = (int)$_POST['delete_photo'];
        
        // Get photo info
        $photo_query = "SELECT * FROM pet_photos WHERE id = ? AND pet_id = ?";
        $photo_stmt = $conn->prepare($photo_query);
        $photo_stmt->bind_param("ii", $photo_id, $pet_id);
        $photo_stmt->execute();
        $photo_result = $photo_stmt->get_result();
        
        if ($photo_result->num_rows > 0) {
            $photo_data = $photo_result->fetch_assoc();
            
            // Delete file
            $file_path = '../uploads/pets/' . $photo_data['photo_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $delete_query = "DELETE FROM pet_photos WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $photo_id);
            $delete_stmt->execute();
            
            // If this was primary photo, make another photo primary
            if ($photo_data['is_primary']) {
                $update_primary = "UPDATE pet_photos SET is_primary = 1 WHERE pet_id = ? LIMIT 1";
                $primary_stmt = $conn->prepare($update_primary);
                $primary_stmt->bind_param("i", $pet_id);
                $primary_stmt->execute();
            }
            
            $success_msg = "Photo deleted successfully.";
        }
        
        // Refresh photos list
        $photos_stmt->execute();
        $photos = $photos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Handle setting primary photo
    if (isset($_POST['set_primary'])) {
        $photo_id = (int)$_POST['set_primary'];
        
        // Remove primary from all photos
        $remove_primary = "UPDATE pet_photos SET is_primary = 0 WHERE pet_id = ?";
        $remove_stmt = $conn->prepare($remove_primary);
        $remove_stmt->bind_param("i", $pet_id);
        $remove_stmt->execute();
        
        // Set new primary
        $set_primary = "UPDATE pet_photos SET is_primary = 1 WHERE id = ? AND pet_id = ?";
        $primary_stmt = $conn->prepare($set_primary);
        $primary_stmt->bind_param("ii", $photo_id, $pet_id);
        $primary_stmt->execute();
        
        $success_msg = "Primary photo updated successfully.";
        
        // Refresh photos list
        $photos_stmt->execute();
        $photos = $photos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Handle regular form submission
    if (isset($_POST['update_pet'])) {
        // Validate pet name
        if (empty(trim($_POST["name"]))) {
            $name_err = "Please enter your pet's name.";
        } else {
            $name = trim($_POST["name"]);
        }
        
        // Validate pet type
        if (empty($_POST["pet_type_id"])) {
            $pet_type_err = "Please select a pet type.";
        } else {
            $pet_type_id = (int)$_POST["pet_type_id"];
        }
        
        // Get other form data
        $breed_id = !empty($_POST["breed_id"]) ? (int)$_POST["breed_id"] : null;
        $gender = $_POST["gender"] ?? 'unknown';
        $date_of_birth = !empty($_POST["date_of_birth"]) ? $_POST["date_of_birth"] : null;
        $weight = !empty($_POST["weight"]) ? (float)$_POST["weight"] : null;
        $color = trim($_POST["color"] ?? '');
        $microchip_number = trim($_POST["microchip_number"] ?? '');
        $description = trim($_POST["description"] ?? '');
        
        // Handle new photo upload
        if (isset($_FILES['new_photos']) && $_FILES['new_photos']['error'][0] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $upload_dir = '../uploads/pets/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            foreach ($_FILES['new_photos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['new_photos']['error'][$key] == 0) {
                    $file_type = $_FILES['new_photos']['type'][$key];
                    $file_size = $_FILES['new_photos']['size'][$key];
                    
                    if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                        $file_extension = pathinfo($_FILES['new_photos']['name'][$key], PATHINFO_EXTENSION);
                        $photo_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $photo_filename;
                        
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            // Insert into database
                            $is_primary = empty($photos) ? 1 : 0; // First photo becomes primary
                            $photo_insert = "INSERT INTO pet_photos (pet_id, photo_url, is_primary) VALUES (?, ?, ?)";
                            $photo_stmt = $conn->prepare($photo_insert);
                            $photo_stmt->bind_param("isi", $pet_id, $photo_filename, $is_primary);
                            $photo_stmt->execute();
                        }
                    }
                }
            }
        }
        
        // If no errors, update the pet
        if (empty($name_err) && empty($pet_type_err)) {
            $update_query = "UPDATE pet_profiles SET name = ?, pet_type_id = ?, breed_id = ?, gender = ?, 
                            date_of_birth = ?, weight = ?, color = ?, microchip_number = ?, description = ?, 
                            updated_at = CURRENT_TIMESTAMP 
                            WHERE id = ? AND user_id = ?";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("siissdssiii", $name, $pet_type_id, $breed_id, $gender, $date_of_birth, 
                             $weight, $color, $microchip_number, $description, $pet_id, $user_id);
            
            if ($stmt->execute()) {
                $success_msg = "Pet updated successfully!";
                
                // Refresh pet data
                $pet_stmt->execute();
                $pet = $pet_stmt->get_result()->fetch_assoc();
                
                // Refresh photos
                $photos_stmt->execute();
                $photos = $photos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                // Redirect after 2 seconds
                header("refresh:2;url=view_pet.php?id=" . $pet_id);
            } else {
                $error_msg = "Something went wrong. Please try again.";
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="edit-pet-page">
    <div class="container py-5">
        
        <!-- Back Navigation -->
        <div class="back-nav mb-4">
            <a href="view_pet.php?id=<?php echo $pet_id; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to <?php echo htmlspecialchars($pet['name']); ?>
            </a>
        </div>
        
        <!-- Page Header -->
        <div class="page-header text-center mb-5">
            <div class="header-icon">
                <i class="fas fa-edit"></i>
            </div>
            <h1 class="page-title">Edit <?php echo htmlspecialchars($pet['name']); ?></h1>
            <p class="page-subtitle">Update your pet's information and photos</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-modern" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_msg; ?>
                <?php if (strpos($success_msg, 'Pet updated') !== false): ?>
                    <div class="mt-2">
                        <small><i class="fas fa-info-circle me-1"></i>Redirecting to pet profile...</small>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-modern" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column - Pet Information -->
            <div class="col-lg-8">
                <div class="form-card">
                    <h3 class="form-card-title">
                        <i class="fas fa-info-circle me-2"></i>Pet Information
                    </h3>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $pet_id; ?>" method="post" enctype="multipart/form-data" class="pet-form">
                        <input type="hidden" name="update_pet" value="1">
                        
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h4 class="section-title">Basic Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Pet Name *</label>
                                        <input type="text" 
                                               name="name" 
                                               id="name" 
                                               class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                               value="<?php echo htmlspecialchars($name); ?>"
                                               placeholder="Enter your pet's name">
                                        <div class="invalid-feedback"><?php echo $name_err; ?></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pet_type_id" class="form-label">Pet Type *</label>
                                        <select name="pet_type_id" 
                                                id="pet_type_id" 
                                                class="form-select <?php echo (!empty($pet_type_err)) ? 'is-invalid' : ''; ?>"
                                                onchange="loadBreeds(this.value)">
                                            <option value="">Select pet type</option>
                                            <?php foreach ($pet_types as $type): ?>
                                                <option value="<?php echo $type['id']; ?>" 
                                                        <?php echo ($pet_type_id == $type['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($type['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback"><?php echo $pet_type_err; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="breed_id" class="form-label">Breed</label>
                                        <select name="breed_id" id="breed_id" class="form-select">
                                            <option value="">Select breed (optional)</option>
                                            <?php foreach ($breeds as $breed): ?>
                                                <option value="<?php echo $breed['id']; ?>" 
                                                        <?php echo ($breed_id == $breed['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($breed['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select name="gender" id="gender" class="form-select">
                                            <option value="unknown" <?php echo ($gender == 'unknown') ? 'selected' : ''; ?>>Unknown</option>
                                            <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Physical Details -->
                        <div class="form-section">
                            <h4 class="section-title">Physical Details</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" 
                                               name="date_of_birth" 
                                               id="date_of_birth" 
                                               class="form-control"
                                               value="<?php echo htmlspecialchars($date_of_birth); ?>"
                                               max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="weight" class="form-label">Weight (kg)</label>
                                        <input type="number" 
                                               name="weight" 
                                               id="weight" 
                                               class="form-control"
                                               value="<?php echo htmlspecialchars($weight); ?>"
                                               step="0.1"
                                               min="0"
                                               placeholder="e.g., 2.5">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="color" class="form-label">Color/Markings</label>
                                        <input type="text" 
                                               name="color" 
                                               id="color" 
                                               class="form-control"
                                               value="<?php echo htmlspecialchars($color); ?>"
                                               placeholder="e.g., Brown & White">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="form-section">
                            <h4 class="section-title">Additional Information</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="microchip_number" class="form-label">Microchip Number</label>
                                        <input type="text" 
                                               name="microchip_number" 
                                               id="microchip_number" 
                                               class="form-control"
                                               value="<?php echo htmlspecialchars($microchip_number); ?>"
                                               placeholder="Enter microchip ID">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-label">Description/Notes</label>
                                <textarea name="description" 
                                          id="description" 
                                          class="form-control"
                                          rows="4"
                                          placeholder="Tell us about your pet's personality, habits, or any special notes..."><?php echo htmlspecialchars($description); ?></textarea>
                            </div>
                        </div>

                        <!-- Add New Photos -->
                        <div class="form-section">
                            <h4 class="section-title">Add New Photos</h4>
                            <div class="upload-area">
                                <label for="new_photos" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt fa-2x"></i>
                                    <p>Click to select multiple photos</p>
                                    <small>JPG, PNG, or GIF (max 5MB each)</small>
                                </label>
                                <input type="file" name="new_photos[]" id="new_photos" accept="image/*" multiple class="file-input">
                            </div>
                            <div id="photoPreview" class="photo-preview-grid"></div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="view_pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Update Pet
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column - Current Photos -->
            <div class="col-lg-4">
                <div class="form-card">
                    <h3 class="form-card-title">
                        <i class="fas fa-camera me-2"></i>Current Photos (<?php echo count($photos); ?>)
                    </h3>
                    
                    <?php if (empty($photos)): ?>
                        <div class="empty-photos">
                            <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                            <p>No photos uploaded yet</p>
                            <small class="text-muted">Use the form to add your first photo</small>
                        </div>
                    <?php else: ?>
                        <div class="current-photos">
                            <?php foreach ($photos as $photo): ?>
                                <div class="photo-item">
                                    <div class="photo-container">
                                        <img src="../uploads/pets/<?php echo htmlspecialchars($photo['photo_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                        
                                        <?php if ($photo['is_primary']): ?>
                                            <div class="primary-badge">
                                                <i class="fas fa-star"></i> Primary
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="photo-overlay">
                                            <?php if (!$photo['is_primary']): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="set_primary" value="<?php echo $photo['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Set as primary">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this photo?')">
                                                <input type="hidden" name="delete_photo" value="<?php echo $photo['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete photo">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <?php if ($photo['caption']): ?>
                                        <div class="photo-caption">
                                            <?php echo htmlspecialchars($photo['caption']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Edit Pet Page Styles */
.edit-pet-page {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    min-height: 100vh;
}

.back-nav .btn {
    border-radius: 10px;
    font-weight: 600;
}

.page-header {
    margin-bottom: 3rem;
}

.header-icon {
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

.form-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
    height: fit-content;
}

.form-card-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.form-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #f1f5f9;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1.5rem;
}

/* Form Controls */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.form-control,
.form-select {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #e53e3e;
}

.invalid-feedback {
    display: block;
    font-size: 0.875rem;
    color: #e53e3e;
    margin-top: 0.25rem;
}

/* Upload Area */
.upload-area {
    margin-bottom: 1.5rem;
}

.upload-label {
    display: block;
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #718096;
}

.upload-label:hover {
    border-color: #667eea;
    background-color: #f8f9ff;
    color: #667eea;
}

.upload-label p {
    margin: 0.5rem 0;
    font-weight: 600;
}

.upload-label small {
    color: #a0aec0;
}

.file-input {
    display: none;
}

.photo-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Current Photos */
.empty-photos {
    text-align: center;
    padding: 3rem 1rem;
    color: #718096;
}

.current-photos {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.photo-item {
    position: relative;
}

.photo-container {
    position: relative;
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.photo-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.primary-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
    color: #d63031;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.photo-overlay {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-container:hover .photo-overlay {
    opacity: 1;
}

.photo-overlay .btn {
    border-radius: 6px;
    padding: 0.25rem 0.5rem;
}

.photo-caption {
    padding: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 12px 12px;
    font-size: 0.9rem;
    color: #4a5568;
}

/* Form Actions */
.form-actions {
    margin-top: 3rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.btn-lg {
    padding: 0.875rem 2rem;
    font-size: 1.1rem;
    border-radius: 10px;
    font-weight: 600;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #f1f5f9;
    border: none;
    color: #718096;
}

.btn-secondary:hover {
    background: #e2e8f0;
    color: #4a5568;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-card {
        padding: 1.5rem;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-lg {
        width: 100%;
    }
    
    .photo-overlay {
        opacity: 1;
    }
    
    .current-photos {
        gap: 1rem;
    }
}
</style>

<script>
// Load breeds based on pet type
function loadBreeds(petTypeId) {
    const breedSelect = document.getElementById('breed_id');
    const currentBreedId = <?php echo $breed_id ? $breed_id : 'null'; ?>;
    
    // Clear existing options
    breedSelect.innerHTML = '<option value="">Loading breeds...</option>';
    
    if (petTypeId) {
        // Make AJAX request to get breeds
        fetch('get_breeds.php?pet_type_id=' + petTypeId)
            .then(response => response.json())
            .then(breeds => {
                breedSelect.innerHTML = '<option value="">Select breed (optional)</option>';
                breeds.forEach(breed => {
                    const option = document.createElement('option');
                    option.value = breed.id;
                    option.textContent = breed.name;
                    if (breed.id == currentBreedId) {
                        option.selected = true;
                    }
                    breedSelect.appendChild(option);
                });
            })
            .catch(error => {
                breedSelect.innerHTML = '<option value="">Error loading breeds</option>';
            });
    } else {
        breedSelect.innerHTML = '<option value="">Select breed (optional)</option>';
    }
}

// Photo upload preview
document.getElementById('new_photos').addEventListener('change', function(e) {
    const files = e.target.files;
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    }
});

// Form validation
document.querySelector('.pet-form').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Check required fields
    const name = document.getElementById('name');
    const petType = document.getElementById('pet_type_id');
    
    if (!name.value.trim()) {
        name.classList.add('is-invalid');
        isValid = false;
    } else {
        name.classList.remove('is-invalid');
    }
    
    if (!petType.value) {
        petType.classList.add('is-invalid');
        isValid = false;
    } else {
        petType.classList.remove('is-invalid');
    }
    
    if (!isValid) {
        e.preventDefault();
        // Scroll to first error
        document.querySelector('.is-invalid').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
    }
});

// Real-time validation
document.getElementById('name').addEventListener('input', function() {
    if (this.value.trim()) {
        this.classList.remove('is-invalid');
    }
});

document.getElementById('pet_type_id').addEventListener('change', function() {
    if (this.value) {
        this.classList.remove('is-invalid');
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>