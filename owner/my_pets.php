<?php
// My Pets - Display all user's pets with filtering and sorting
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

// Get filter parameters
$filter_type = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$where_conditions = ["p.user_id = ? AND p.is_active = 1"];
$params = [$user_id];
$param_types = "i";

if ($filter_type > 0) {
    $where_conditions[] = "p.pet_type_id = ?";
    $params[] = $filter_type;
    $param_types .= "i";
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.color LIKE ? OR pb.name LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $param_types .= "sss";
}

// Determine sort order
$order_by = "p.name ASC";
switch ($sort_by) {
    case 'name':
        $order_by = "p.name ASC";
        break;
    case 'type':
        $order_by = "pt.name ASC, p.name ASC";
        break;
    case 'age':
        $order_by = "p.date_of_birth DESC";
        break;
    case 'newest':
        $order_by = "p.created_at DESC";
        break;
}

$pets_query = "SELECT p.*, pt.name as pet_type, pt.icon as type_icon, pb.name as breed_name,
                      (SELECT photo_url FROM pet_photos pp WHERE pp.pet_id = p.id AND pp.is_primary = 1 LIMIT 1) as profile_image
               FROM pet_profiles p 
               LEFT JOIN pet_types pt ON p.pet_type_id = pt.id
               LEFT JOIN pet_breeds pb ON p.breed_id = pb.id
               WHERE " . implode(" AND ", $where_conditions) . "
               ORDER BY $order_by";

$pets_stmt = $conn->prepare($pets_query);
if (!empty($params)) {
    $pets_stmt->bind_param($param_types, ...$params);
}
$pets_stmt->execute();
$pets = $pets_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get pet types for filter dropdown
$types_query = "SELECT * FROM pet_types ORDER BY name";
$types_result = $conn->query($types_query);
$pet_types = $types_result->fetch_all(MYSQLI_ASSOC);

// Include header
include '../includes/header.php';
?>

<div class="my-pets-page">
    <div class="container py-5">
        
        <!-- Page Header -->
        <div class="page-header mb-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">
                        <i class="fas fa-heart me-3"></i>My Pets
                    </h1>
                    <p class="page-subtitle">
                        Showing <?php echo count($pets); ?> of your beloved companions
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="add_pet.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Add New Pet
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="filters-section mb-4">
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="search-box">
                        <form method="GET" action="" class="search-form">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Search pets by name, color, breed..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <input type="hidden" name="type" value="<?php echo $filter_type; ?>">
                                <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
                                <button type="submit" class="btn btn-outline-primary">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="filter-dropdown">
                        <label class="form-label">Filter by Type:</label>
                        <select class="form-select" onchange="filterByType(this.value)">
                            <option value="0">All Types</option>
                            <?php foreach ($pet_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" 
                                        <?php echo ($filter_type == $type['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="sort-dropdown">
                        <label class="form-label">Sort by:</label>
                        <select class="form-select" onchange="sortBy(this.value)">
                            <option value="name" <?php echo ($sort_by == 'name') ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="type" <?php echo ($sort_by == 'type') ? 'selected' : ''; ?>>Pet Type</option>
                            <option value="age" <?php echo ($sort_by == 'age') ? 'selected' : ''; ?>>Age (Youngest First)</option>
                            <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Recently Added</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pets Grid -->
        <?php if (empty($pets)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-paw"></i>
                </div>
                <?php if (!empty($search) || $filter_type > 0): ?>
                    <h3>No pets found</h3>
                    <p>Try adjusting your search terms or filters.</p>
                    <div class="empty-actions">
                        <a href="my_pets.php" class="btn btn-outline-primary me-2">Clear Filters</a>
                        <a href="add_pet.php" class="btn btn-primary">Add New Pet</a>
                    </div>
                <?php else: ?>
                    <h3>No pets added yet</h3>
                    <p>Start building your pet family by adding your first companion.</p>
                    <a href="add_pet.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Add Your First Pet
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="pets-grid">
                <?php foreach ($pets as $pet): ?>
                    <div class="pet-card" data-aos="fade-up">
                        <div class="pet-header">
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
                            <div class="pet-type-badge">
                                <i class="<?php echo htmlspecialchars($pet['type_icon']); ?> me-1"></i>
                                <?php echo htmlspecialchars($pet['pet_type']); ?>
                            </div>
                        </div>
                        
                        <div class="pet-content">
                            <h4 class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></h4>
                            
                            <div class="pet-details">
                                <?php if ($pet['breed_name']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-dna me-1"></i>
                                        <span><?php echo htmlspecialchars($pet['breed_name']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($pet['gender'] !== 'unknown'): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-<?php echo ($pet['gender'] == 'male') ? 'mars' : 'venus'; ?> me-1"></i>
                                        <span><?php echo ucfirst($pet['gender']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($pet['date_of_birth']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-birthday-cake me-1"></i>
                                        <span>
                                            <?php 
                                            $birth_date = new DateTime($pet['date_of_birth']);
                                            $today = new DateTime();
                                            $age = $birth_date->diff($today);
                                            if ($age->y > 0) {
                                                echo $age->y . ' year' . ($age->y > 1 ? 's' : '');
                                                if ($age->m > 0) echo ', ' . $age->m . ' month' . ($age->m > 1 ? 's' : '');
                                            } else {
                                                echo $age->m . ' month' . ($age->m > 1 ? 's' : '');
                                            }
                                            ?> old
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($pet['weight']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-weight me-1"></i>
                                        <span><?php echo $pet['weight']; ?> kg</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($pet['color']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-palette me-1"></i>
                                        <span><?php echo htmlspecialchars($pet['color']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($pet['description']): ?>
                                <div class="pet-description">
                                    <p><?php echo htmlspecialchars(substr($pet['description'], 0, 100)); ?>
                                    <?php echo strlen($pet['description']) > 100 ? '...' : ''; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pet-actions">
                            <a href="view_pet.php?id=<?php echo $pet['id']; ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            <a href="pet_medical.php?id=<?php echo $pet['id']; ?>" 
                               class="btn btn-outline-success">
                                <i class="fas fa-file-medical me-1"></i>Medical
                            </a>
                            <a href="edit_pet.php?id=<?php echo $pet['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add AOS for animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
/* My Pets Page Styles */
.my-pets-page {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    min-height: 100vh;
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

/* Filters Section */
.filters-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.search-form .input-group {
    border-radius: 10px;
    overflow: hidden;
}

.search-form .form-control {
    border: 2px solid #e2e8f0;
    border-right: none;
}

.search-form .input-group-text {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-right: none;
    color: #718096;
}

.search-form .btn {
    border: 2px solid #e2e8f0;
    border-left: none;
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
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

/* Pets Grid */
.pets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.pet-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    position: relative;
}

.pet-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.pet-header {
    position: relative;
    padding: 2rem 2rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
}

.pet-image {
    margin-bottom: 1rem;
}

.pet-image img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.pet-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 2.5rem;
    border: 4px solid white;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.pet-type-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.9);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #667eea;
    backdrop-filter: blur(10px);
}

.pet-content {
    padding: 0 2rem 1.5rem;
}

.pet-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
    text-align: center;
}

.pet-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    color: #718096;
}

.detail-item i {
    color: #667eea;
    width: 16px;
}

.pet-description {
    margin-bottom: 1rem;
}

.pet-description p {
    font-size: 0.9rem;
    color: #718096;
    line-height: 1.6;
    margin: 0;
}

.pet-actions {
    padding: 1.5rem 2rem 2rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.pet-actions .btn {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    font-weight: 600;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-outline-primary {
    border-color: #667eea;
    color: #667eea;
}

.btn-outline-success {
    border-color: #4facfe;
    color: #4facfe;
}

/* Responsive Design */
@media (max-width: 768px) {
    .pets-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header .row {
        text-align: center;
    }
    
    .page-header .col-md-4 {
        margin-top: 1rem;
    }
    
    .filters-section {
        padding: 1.5rem;
    }
    
    .empty-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .pet-actions {
        flex-direction: column;
    }
    
    .pet-actions .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 2rem;
    }
    
    .pet-card {
        margin: 0 -1rem;
        border-radius: 16px;
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
function filterByType(typeId) {
    const url = new URL(window.location);
    url.searchParams.set('type', typeId);
    url.searchParams.delete('search'); // Clear search when changing type
    window.location.href = url.toString();
}

function sortBy(sortOption) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortOption);
    window.location.href = url.toString();
}

// Search form enhancement
document.querySelector('.search-form').addEventListener('submit', function(e) {
    const searchInput = this.querySelector('input[name="search"]');
    if (!searchInput.value.trim()) {
        e.preventDefault();
        // Clear search if empty
        const url = new URL(window.location);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    }
});

// Clear search on escape key
document.querySelector('input[name="search"]').addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        this.form.submit();
    }
});

// Auto-submit search after typing (debounced)
let searchTimeout;
document.querySelector('input[name="search"]').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (this.value.length >= 3 || this.value.length === 0) {
            this.form.submit();
        }
    }, 500);
});
</script>

<?php
// Include footer
include '../includes/footer.php';

// Close connection
$conn->close();
?>