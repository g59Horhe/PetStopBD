<?php
// Add Pet Form - Create new pet profile
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

// Initialize variables
$name = $pet_type_id = $breed_id = $gender = $date_of_birth = $weight = $color = $microchip_number = $description = "";
$name_err = $pet_type_err = $success_msg = $error_msg = "";

// Get pet types for dropdown
$types_query = "SELECT * FROM pet_types ORDER BY name";
$types_result = $conn->query($types_query);
$pet_types = $types_result->fetch_all(MYSQLI_ASSOC);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
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
    
    // Handle file upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['profile_image']['type'], $allowed_types) && $_FILES['profile_image']['size'] <= $max_size) {
            $upload_dir = '../uploads/pets/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $profile_image = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $profile_image;
            
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $error_msg = "Failed to upload image.";
            }
        } else {
            $error_msg = "Invalid image file. Please upload JPG, PNG, or GIF under 5MB.";
        }
    }
    
    // If no errors, insert the pet
    if (empty($name_err) && empty($pet_type_err) && empty($error_msg)) {
        $insert_query = "INSERT INTO pet_profiles (user_id, name, pet_type_id, breed_id, gender, date_of_birth, weight, color, microchip_number, description, profile_image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isiissdssss", $user_id, $name, $pet_type_id, $breed_id, $gender, $date_of_birth, $weight, $color, $microchip_number, $description, $profile_image);
        
        if ($stmt->execute()) {
            $pet_id = $conn->insert_id;
            
            // If image was uploaded, also add to pet_photos table
            if ($profile_image) {
                $photo_query = "INSERT INTO pet_photos (pet_id, photo_url, is_primary) VALUES (?, ?, 1)";
                $photo_stmt = $conn->prepare($photo_query);
                $photo_stmt->bind_param("is", $pet_id, $profile_image);
                $photo_stmt->execute();
            }
            
            $success_msg = "Pet added successfully!";
            
            // Redirect to pet view after 2 seconds
            header("refresh:2;url=view_pet.php?id=" . $pet_id);
        } else {
            $error_msg = "Something went wrong. Please try again.";
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="add-pet-page">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Page Header -->
                <div class="page-header text-center mb-5">
                    <div class="header-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h1 class="page-title">Add New Pet</h1>
                    <p class="page-subtitle">Create a profile for your beloved companion</p>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success alert-modern" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success_msg; ?>
                        <div class="mt-2">
                            <small><i class="fas fa-info-circle me-1"></i>Redirecting to pet profile...</small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-modern" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Pet Form -->
                <div class="form-card">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="pet-form">
                        
                        <!-- Pet Photo Upload -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-camera me-2"></i>Pet Photo
                            </h3>
                            <div class="photo-upload-area">
                                <div class="photo-preview" id="photoPreview">
                                    <div class="upload-placeholder">
                                        <i class="fas fa-camera fa-2x"></i>
                                        <p>Click to upload photo</p>
                                        <small>JPG, PNG, or GIF (max 5MB)</small>
                                    </div>
                                </div>
                                <input type="file" name="profile_image" id="profileImage" accept="image/*" class="photo-input">
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fas fa-info-circle me-2"></i>Basic Information
                            </h3>
                            
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
                            <h3 class="section-title">
                                <i class="fas fa-ruler me-2"></i>Physical Details
                            </h3>
                            
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
                            <h3 class="section-title">
                                <i class="fas fa-clipboard me-2"></i>Additional Information
                            </h3>
                            
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

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Add Pet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Add Pet Page Styles */
.add-pet-page {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    min-height: 100vh;
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
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.section-title i {
    color: #667eea;
}

/* Photo Upload */
.photo-upload-area {
    text-align: center;
    margin-bottom: 1rem;
}

.photo-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 3px dashed #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.photo-preview:hover {
    border-color: #667eea;
    background-color: #f8f9ff;
}

.photo-preview.has-image {
    border-style: solid;
    border-color: #667eea;
}

.upload-placeholder {
    text-align: center;
    color: #718096;
}

.upload-placeholder p {
    margin: 0.5rem 0;
    font-weight: 500;
}

.upload-placeholder small {
    font-size: 0.8rem;
    color: #a0aec0;
}

.photo-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

textarea.form-control {
    resize: vertical;
    min-height: 120px;
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
}
</style>

<script>
// Photo upload preview
document.getElementById('profileImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('photoPreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Pet photo">';
            preview.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    }
});

// Make photo preview clickable
document.getElementById('photoPreview').addEventListener('click', function() {
    document.getElementById('profileImage').click();
});

// Load breeds based on pet type
function loadBreeds(petTypeId) {
    const breedSelect = document.getElementById('breed_id');
    
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