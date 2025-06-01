<?php
// Include database connection
require_once 'config/db_connect.php';

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        profile_image VARCHAR(255),
        phone VARCHAR(20),
        address TEXT,
        city VARCHAR(50),
        country VARCHAR(50),
        bio TEXT,
        user_type ENUM('admin', 'regular', 'vet', 'shop_owner') DEFAULT 'regular',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(255),
        category VARCHAR(50),
        status ENUM('published', 'draft') DEFAULT 'published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        title VARCHAR(100),
        content TEXT NOT NULL,
        avatar VARCHAR(255),
        rating INT,
        status ENUM('approved', 'pending') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

// Create each table
foreach ($tables as $sql) {
    if ($conn->query($sql) !== TRUE) {
        die("Error creating table: " . $conn->error);
    }
}

echo "Tables created successfully!<br>";

// Check if admin user exists
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
$row = $result->fetch_assoc();

// Insert admin user if it doesn't exist
if ($row['count'] == 0) {
    $adminSql = "INSERT INTO users (username, email, password, first_name, last_name, user_type) VALUES
                ('admin', 'admin@petstopbd.com', '$2y$10$8IjbZ73YIqDYbz9qAzO3Gu2dnPdRhpPyaKcr.k1XwXlW1a.JSI.vy', 'Admin', 'User', 'admin')";
    
    if ($conn->query($adminSql) !== TRUE) {
        die("Error inserting admin user: " . $conn->error);
    }
    
    echo "Admin user created successfully!<br>";
}

// Blog posts data
$blogPosts = [
    [
        'title' => 'How to Care for a Rescue Pet',
        'content' => 'When you adopt a rescue pet, you\'re giving an animal a second chance at a happy life. However, rescue pets often come with past experiences that may affect their behavior and adjustment to a new home. Here are some essential tips to help your new pet adjust and thrive in their forever home...\n\nProvide a quiet, safe space: Rescue animals need time to decompress. Set up a quiet area with their bed, food, water, and toys where they can retreat when feeling overwhelmed.\n\nEstablish a routine: Consistent feeding times, walks, and play sessions help your pet feel secure in their new environment.\n\nBe patient: Some pets may take weeks or even months to fully adjust. Don\'t rush the process or force interactions.\n\nPositive reinforcement: Reward good behavior with treats, praise, and affection to build trust and confidence.',
        'image_url' => 'img/blog-1.jpg',
        'category' => 'Adoption'
    ],
    [
        'title' => 'Essential Vaccinations for Dogs in Bangladesh',
        'content' => 'Keeping your dog healthy in Bangladesh requires a proper vaccination schedule. Here\'s what every dog owner should know about essential vaccinations...\n\nRabies: This vaccination is not only crucial for your pet\'s health but is also required by law in many areas. Rabies is fatal and can be transmitted to humans, making this vaccine absolutely essential.\n\nDistemper: This highly contagious and potentially fatal disease affects a dog\'s respiratory, gastrointestinal, and nervous systems.\n\nParvovirus: Particularly dangerous for puppies, parvovirus causes severe, often fatal gastrointestinal illness.\n\nAdenovirus: This protects against infectious canine hepatitis, which can cause liver damage.\n\nConsult with your veterinarian to establish the right vaccination schedule for your dog based on their age, health status, and lifestyle.',
        'image_url' => 'img/blog-2.jpg',
        'category' => 'Pet Health'
    ],
    [
        'title' => 'Creating an Enriching Environment for Your Cat',
        'content' => 'Cats need mental and physical stimulation to stay happy and healthy. Here are ways to create an enriching environment for your feline friend...\n\nVertical space: Cats love to climb and observe from high places. Cat trees, shelves, and perches give them the vertical territory they crave.\n\nHiding spots: Provide boxes, tunnels, or cat caves where your cat can retreat when they want privacy.\n\nInteractive toys: Toys that mimic prey movements engage your cat\'s hunting instincts. Rotate toys regularly to maintain interest.\n\nWindow views: Position perches near windows so your cat can watch birds, insects, and outdoor activities.\n\nScratch-friendly surfaces: Provide a variety of scratching posts with different textures and orientations (horizontal and vertical) to satisfy your cat\'s scratching needs.',
        'image_url' => 'img/blog-3.jpg',
        'category' => 'Cat Care'
    ]
];

// Check if blog posts exist
$result = $conn->query("SELECT COUNT(*) as count FROM blog_posts");
$row = $result->fetch_assoc();

// Insert blog posts if they don't exist
if ($row['count'] == 0) {
    foreach ($blogPosts as $post) {
        $title = $conn->real_escape_string($post['title']);
        $content = $conn->real_escape_string($post['content']);
        $image_url = $conn->real_escape_string($post['image_url']);
        $category = $conn->real_escape_string($post['category']);
        
        $blogSql = "INSERT INTO blog_posts (user_id, title, content, image_url, category) 
                    VALUES (1, '$title', '$content', '$image_url', '$category')";
        
        if ($conn->query($blogSql) !== TRUE) {
            die("Error inserting blog post: " . $conn->error);
        }
    }
    
    echo "Blog posts created successfully!<br>";
}

// Testimonials data
$testimonials = [
    [
        'name' => 'Sarah Ahmed',
        'title' => 'Cat Owner',
        'content' => 'PetStopBD helped me find my perfect feline companion. The adoption process was smooth, and their support afterward was excellent. I couldn\'t be happier with my new furry friend!',
        'avatar' => 'img/testimonial-1.jpg',
        'rating' => 5
    ],
    [
        'name' => 'Karim Rahman',
        'title' => 'Volunteer',
        'content' => 'When I found an injured stray dog, I didn\'t know what to do. PetStopBD\'s rescue team guided me through the process and helped save the pup. They\'re doing amazing work for animals in Bangladesh.',
        'avatar' => 'img/testimonial-2.jpg',
        'rating' => 5
    ],
    [
        'name' => 'Nusrat Jahan',
        'title' => 'Reptile Owner',
        'content' => 'The vet directory helped me find specialized care for my exotic pet. I finally found a vet who understands reptiles! The online pharmacy is also very convenient for regular medications.',
        'avatar' => 'img/testimonial-3.jpg',
        'rating' => 4
    ]
];

// Check if testimonials exist
$result = $conn->query("SELECT COUNT(*) as count FROM testimonials");
$row = $result->fetch_assoc();

// Insert testimonials if they don't exist
if ($row['count'] == 0) {
    foreach ($testimonials as $testimonial) {
        $name = $conn->real_escape_string($testimonial['name']);
        $title = $conn->real_escape_string($testimonial['title']);
        $content = $conn->real_escape_string($testimonial['content']);
        $avatar = $conn->real_escape_string($testimonial['avatar']);
        $rating = $testimonial['rating'];
        
        $testimonialSql = "INSERT INTO testimonials (name, title, content, avatar, rating, status) 
                          VALUES ('$name', '$title', '$content', '$avatar', $rating, 'approved')";
        
        if ($conn->query($testimonialSql) !== TRUE) {
            die("Error inserting testimonial: " . $conn->error);
        }
    }
    
    echo "Testimonials created successfully!<br>";
}

echo "<p>Database setup completed successfully!</p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";

// Close connection
$conn->close();
?>
