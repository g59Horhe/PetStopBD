<?php
// Owner Section Database Setup for PetStopBD
// Run this file to set up the owner section database tables and sample data

// Include database connection
require_once 'config/db_connect.php';

echo "<h2>Setting up Owner Section Database...</h2>";

// Create tables array
$tables = [
    // Pet Types Reference Table
    "CREATE TABLE IF NOT EXISTS pet_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Pet Breeds Reference Table
    "CREATE TABLE IF NOT EXISTS pet_breeds (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_type_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        size_category ENUM('tiny', 'small', 'medium', 'large', 'giant') DEFAULT 'medium',
        life_expectancy_min INT,
        life_expectancy_max INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pet_type_id) REFERENCES pet_types(id) ON DELETE CASCADE,
        UNIQUE KEY unique_breed_per_type (pet_type_id, name)
    )",
    
    // Pet Profiles Main Table
    "CREATE TABLE IF NOT EXISTS pet_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        pet_type_id INT NOT NULL,
        breed_id INT,
        gender ENUM('male', 'female', 'unknown') DEFAULT 'unknown',
        date_of_birth DATE,
        weight DECIMAL(5,2),
        color VARCHAR(100),
        microchip_number VARCHAR(50),
        description TEXT,
        profile_image VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (pet_type_id) REFERENCES pet_types(id),
        FOREIGN KEY (breed_id) REFERENCES pet_breeds(id) ON DELETE SET NULL
    )",
    
    // Pet Photos Table
    "CREATE TABLE IF NOT EXISTS pet_photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_id INT NOT NULL,
        photo_url VARCHAR(255) NOT NULL,
        caption VARCHAR(255),
        is_primary BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pet_id) REFERENCES pet_profiles(id) ON DELETE CASCADE
    )",
    
    // Medical Records Table
    "CREATE TABLE IF NOT EXISTS medical_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_id INT NOT NULL,
        record_type ENUM('vaccination', 'checkup', 'treatment', 'surgery', 'medication', 'other') NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        date_performed DATE NOT NULL,
        veterinarian_name VARCHAR(100),
        clinic_name VARCHAR(100),
        cost DECIMAL(10,2),
        next_due_date DATE,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (pet_id) REFERENCES pet_profiles(id) ON DELETE CASCADE
    )",
    
    // Vaccination Schedule Template
    "CREATE TABLE IF NOT EXISTS vaccination_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_type_id INT NOT NULL,
        vaccine_name VARCHAR(100) NOT NULL,
        first_dose_age_weeks INT,
        booster_interval_months INT,
        is_required BOOLEAN DEFAULT TRUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pet_type_id) REFERENCES pet_types(id) ON DELETE CASCADE
    )"
];

// Create each table
foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating table: " . $conn->error . "</p>";
    }
}

echo "<h3>Inserting Sample Data...</h3>";

// Check if pet types already exist
$result = $conn->query("SELECT COUNT(*) as count FROM pet_types");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "<p>Inserting pet types...</p>";
    
    // Insert Pet Types
    $pet_types = [
        ['Dog', 'Domestic dogs of all breeds and sizes', 'fas fa-dog'],
        ['Cat', 'Domestic cats including all breeds', 'fas fa-cat'],
        ['Bird', 'Pet birds including parrots, canaries, etc.', 'fas fa-dove'],
        ['Fish', 'Aquarium fish and aquatic pets', 'fas fa-fish'],
        ['Rabbit', 'Domestic rabbits and bunnies', 'fas fa-rabbit'],
        ['Hamster', 'Small rodents including hamsters, gerbils', 'fas fa-mouse'],
        ['Reptile', 'Lizards, snakes, turtles and other reptiles', 'fas fa-dragon'],
        ['Other', 'Other types of pets', 'fas fa-paw']
    ];
    
    $type_stmt = $conn->prepare("INSERT INTO pet_types (name, description, icon) VALUES (?, ?, ?)");
    foreach ($pet_types as $type) {
        $type_stmt->bind_param("sss", $type[0], $type[1], $type[2]);
        $type_stmt->execute();
    }
    
    echo "<p style='color: green;'>✓ Pet types inserted</p>";
    
    // Insert Dog Breeds
    echo "<p>Inserting dog breeds...</p>";
    $dog_breeds = [
        ['Golden Retriever', 'large', 10, 12],
        ['Labrador Retriever', 'large', 10, 14],
        ['German Shepherd', 'large', 9, 13],
        ['Bulldog', 'medium', 8, 10],
        ['Poodle', 'medium', 12, 15],
        ['Beagle', 'medium', 12, 15],
        ['Rottweiler', 'large', 8, 10],
        ['Yorkshire Terrier', 'tiny', 13, 16],
        ['Dachshund', 'small', 12, 16],
        ['Siberian Husky', 'large', 12, 14],
        ['Shih Tzu', 'small', 10, 18],
        ['Chihuahua', 'tiny', 14, 16],
        ['Border Collie', 'medium', 12, 15],
        ['Cocker Spaniel', 'medium', 10, 14],
        ['Mixed Breed', 'medium', 10, 13]
    ];
    
    $breed_stmt = $conn->prepare("INSERT INTO pet_breeds (pet_type_id, name, size_category, life_expectancy_min, life_expectancy_max) VALUES (1, ?, ?, ?, ?)");
    foreach ($dog_breeds as $breed) {
        $breed_stmt->bind_param("ssii", $breed[0], $breed[1], $breed[2], $breed[3]);
        $breed_stmt->execute();
    }
    
    // Insert Cat Breeds
    echo "<p>Inserting cat breeds...</p>";
    $cat_breeds = [
        ['Persian', 10, 17],
        ['Maine Coon', 10, 13],
        ['Siamese', 8, 12],
        ['Ragdoll', 12, 15],
        ['British Shorthair', 12, 17],
        ['American Shorthair', 13, 17],
        ['Scottish Fold', 11, 14],
        ['Russian Blue', 10, 16],
        ['Bengal', 12, 16],
        ['Abyssinian', 9, 13],
        ['Domestic Shorthair', 13, 17],
        ['Domestic Longhair', 12, 16]
    ];
    
    $cat_stmt = $conn->prepare("INSERT INTO pet_breeds (pet_type_id, name, life_expectancy_min, life_expectancy_max) VALUES (2, ?, ?, ?)");
    foreach ($cat_breeds as $breed) {
        $cat_stmt->bind_param("sii", $breed[0], $breed[1], $breed[2]);
        $cat_stmt->execute();
    }
    
    // Insert Bird Breeds
    echo "<p>Inserting bird breeds...</p>";
    $bird_breeds = [
        ['Budgerigar', 5, 8],
        ['Cockatiel', 15, 20],
        ['Lovebird', 10, 15],
        ['Canary', 10, 15],
        ['African Grey Parrot', 40, 60],
        ['Macaw', 50, 80],
        ['Conure', 20, 30],
        ['Finch', 5, 10]
    ];
    
    $bird_stmt = $conn->prepare("INSERT INTO pet_breeds (pet_type_id, name, life_expectancy_min, life_expectancy_max) VALUES (3, ?, ?, ?)");
    foreach ($bird_breeds as $breed) {
        $bird_stmt->bind_param("sii", $breed[0], $breed[1], $breed[2]);
        $bird_stmt->execute();
    }
    
    // Insert Fish Types
    echo "<p>Inserting fish types...</p>";
    $fish_types = [
        ['Goldfish', 10, 30],
        ['Betta Fish', 2, 4],
        ['Angelfish', 6, 10],
        ['Guppy', 1, 3],
        ['Neon Tetra', 5, 8],
        ['Oscar Fish', 10, 20],
        ['Molly Fish', 3, 5]
    ];
    
    $fish_stmt = $conn->prepare("INSERT INTO pet_breeds (pet_type_id, name, life_expectancy_min, life_expectancy_max) VALUES (4, ?, ?, ?)");
    foreach ($fish_types as $breed) {
        $fish_stmt->bind_param("sii", $breed[0], $breed[1], $breed[2]);
        $fish_stmt->execute();
    }
    
    // Insert Rabbit Breeds
    echo "<p>Inserting rabbit breeds...</p>";
    $rabbit_breeds = [
        ['Holland Lop', 7, 12],
        ['Mini Rex', 5, 8],
        ['Netherland Dwarf', 10, 12],
        ['Flemish Giant', 5, 8],
        ['Lionhead', 7, 10],
        ['Dutch Rabbit', 5, 8]
    ];
    
    $rabbit_stmt = $conn->prepare("INSERT INTO pet_breeds (pet_type_id, name, life_expectancy_min, life_expectancy_max) VALUES (5, ?, ?, ?)");
    foreach ($rabbit_breeds as $breed) {
        $rabbit_stmt->bind_param("sii", $breed[0], $breed[1], $breed[2]);
        $rabbit_stmt->execute();
    }
    
    echo "<p style='color: green;'>✓ Breeds inserted</p>";
    
    // Insert Vaccination Schedules
    echo "<p>Inserting vaccination schedules...</p>";
    
    // Dog vaccinations
    $dog_vaccines = [
        ['DHPP (Distemper, Hepatitis, Parvovirus, Parainfluenza)', 6, 12, 1, 'Core vaccine protecting against multiple diseases'],
        ['Rabies', 12, 12, 1, 'Required by law in most areas'],
        ['Bordetella (Kennel Cough)', 8, 12, 0, 'Recommended for dogs that socialize with other dogs'],
        ['Lyme Disease', 12, 12, 0, 'Recommended in areas with high tick population']
    ];
    
    $vaccine_stmt = $conn->prepare("INSERT INTO vaccination_schedules (pet_type_id, vaccine_name, first_dose_age_weeks, booster_interval_months, is_required, description) VALUES (1, ?, ?, ?, ?, ?)");
    foreach ($dog_vaccines as $vaccine) {
        $vaccine_stmt->bind_param("siiss", $vaccine[0], $vaccine[1], $vaccine[2], $vaccine[3], $vaccine[4]);
        $vaccine_stmt->execute();
    }
    
    // Cat vaccinations
    $cat_vaccines = [
        ['FVRCP (Feline Viral Rhinotracheitis, Calicivirus, Panleukopenia)', 6, 12, 1, 'Core vaccine for cats'],
        ['Rabies', 12, 12, 1, 'Required by law in most areas'],
        ['FeLV (Feline Leukemia)', 8, 12, 0, 'Recommended for outdoor cats'],
        ['FIV (Feline Immunodeficiency Virus)', 8, 12, 0, 'Recommended for high-risk cats']
    ];
    
    $cat_vaccine_stmt = $conn->prepare("INSERT INTO vaccination_schedules (pet_type_id, vaccine_name, first_dose_age_weeks, booster_interval_months, is_required, description) VALUES (2, ?, ?, ?, ?, ?)");
    foreach ($cat_vaccines as $vaccine) {
        $cat_vaccine_stmt->bind_param("siiss", $vaccine[0], $vaccine[1], $vaccine[2], $vaccine[3], $vaccine[4]);
        $cat_vaccine_stmt->execute();
    }
    
    echo "<p style='color: green;'>✓ Vaccination schedules inserted</p>";
} else {
    echo "<p style='color: orange;'>⚠ Pet types already exist, skipping sample data insertion</p>";
}

// Create indexes for better performance
echo "<h3>Creating Database Indexes...</h3>";

$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_pet_profiles_user_id ON pet_profiles(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_pet_profiles_type ON pet_profiles(pet_type_id)",
    "CREATE INDEX IF NOT EXISTS idx_medical_records_pet_id ON medical_records(pet_id)",
    "CREATE INDEX IF NOT EXISTS idx_medical_records_date ON medical_records(date_performed)",
    "CREATE INDEX IF NOT EXISTS idx_medical_records_next_due ON medical_records(next_due_date)",
    "CREATE INDEX IF NOT EXISTS idx_pet_photos_pet_id ON pet_photos(pet_id)",
    "CREATE INDEX IF NOT EXISTS idx_pet_breeds_type ON pet_breeds(pet_type_id)"
];

foreach ($indexes as $index_sql) {
    if ($conn->query($index_sql) === TRUE) {
        echo "<p style='color: green;'>✓ Index created</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating index: " . $conn->error . "</p>";
    }
}

// Create uploads directory if it doesn't exist
echo "<h3>Setting up File Directories...</h3>";

$upload_dirs = [
    'uploads',
    'uploads/pets'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "<p style='color: green;'>✓ Created directory: $dir</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Directory already exists: $dir</p>";
    }
}

// Check if we need to create a sample user for testing
echo "<h3>Checking Test Data...</h3>";

$user_check = $conn->query("SELECT COUNT(*) as count FROM users WHERE username != 'admin'");
$user_row = $user_check->fetch_assoc();

if ($user_row['count'] == 0) {
    echo "<p style='color: orange;'>⚠ No regular users found. You may want to register a user account to test the pet management features.</p>";
    echo "<p><a href='auth/register.php' style='color: #667eea;'>Click here to register a test account</a></p>";
} else {
    echo "<p style='color: green;'>✓ User accounts found for testing</p>";
}

echo "<h2 style='color: green;'>Owner Section Setup Complete!</h2>";
echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 10px; border-left: 4px solid #3b82f6; margin: 20px 0;'>";
echo "<h4>What's Next?</h4>";
echo "<ol>";
echo "<li><strong>Register/Login:</strong> Create a user account if you haven't already</li>";
echo "<li><strong>Add Your First Pet:</strong> Go to <a href='owner/add_pet.php' style='color: #667eea;'>Add Pet</a> to create your first pet profile</li>";
echo "<li><strong>Explore Features:</strong> Try adding medical records, uploading photos, and managing your pets</li>";
echo "</ol>";
echo "<p><strong>Quick Links:</strong></p>";
echo "<ul>";
echo "<li><a href='owner/index.php' style='color: #667eea;'>Owner Dashboard</a></li>";
echo "<li><a href='owner/my_pets.php' style='color: #667eea;'>My Pets</a></li>";
echo "<li><a href='owner/add_pet.php' style='color: #667eea;'>Add New Pet</a></li>";
echo "</ul>";
echo "</div>";

// Close connection
$conn->close();
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 40px;
    background: #f8fafc;
    color: #2d3748;
}

h2, h3 {
    color: #1a202c;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
}

p {
    margin: 5px 0;
    padding: 5px 10px;
    border-radius: 4px;
}

a {
    text-decoration: none;
    font-weight: 600;
}

a:hover {
    text-decoration: underline;
}

ol, ul {
    line-height: 1.6;
}

li {
    margin-bottom: 8px;
}
</style>