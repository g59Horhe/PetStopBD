-- Database Creation for PetStopBD
-- Run this script to set up the initial database structure

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS petstopbd;

-- Use the database
USE petstopbd;

-- Users table
CREATE TABLE IF NOT EXISTS users (
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
);

-- Blog posts table
CREATE TABLE IF NOT EXISTS blog_posts (
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
);

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(100),
    content TEXT NOT NULL,
    avatar VARCHAR(255),
    rating INT,
    status ENUM('approved', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data for testing
-- Sample admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, user_type) VALUES
('admin', 'admin@petstopbd.com', '$2y$10$8IjbZ73YIqDYbz9qAzO3Gu2dnPdRhpPyaKcr.k1XwXlW1a.JSI.vy', 'Admin', 'User', 'admin');

-- Sample blog posts
INSERT INTO blog_posts (user_id, title, content, image_url, category) VALUES
(1, 'How to Care for a Rescue Pet', 'When you adopt a rescue pet, you're giving an animal a second chance at a happy life. However, rescue pets often come with past experiences that may affect their behavior and adjustment to a new home. Here are some essential tips to help your new pet adjust and thrive in their forever home...\n\nProvide a quiet, safe space: Rescue animals need time to decompress. Set up a quiet area with their bed, food, water, and toys where they can retreat when feeling overwhelmed.\n\nEstablish a routine: Consistent feeding times, walks, and play sessions help your pet feel secure in their new environment.\n\nBe patient: Some pets may take weeks or even months to fully adjust. Don\'t rush the process or force interactions.\n\nPositive reinforcement: Reward good behavior with treats, praise, and affection to build trust and confidence.', 'img/blog-1.jpg', 'Adoption'),
(1, 'Essential Vaccinations for Dogs in Bangladesh', 'Keeping your dog healthy in Bangladesh requires a proper vaccination schedule. Here's what every dog owner should know about essential vaccinations...\n\nRabies: This vaccination is not only crucial for your pet\'s health but is also required by law in many areas. Rabies is fatal and can be transmitted to humans, making this vaccine absolutely essential.\n\nDistemper: This highly contagious and potentially fatal disease affects a dog\'s respiratory, gastrointestinal, and nervous systems.\n\nParvovirus: Particularly dangerous for puppies, parvovirus causes severe, often fatal gastrointestinal illness.\n\nAdenovirus: This protects against infectious canine hepatitis, which can cause liver damage.\n\nConsult with your veterinarian to establish the right vaccination schedule for your dog based on their age, health status, and lifestyle.', 'img/blog-2.jpg', 'Pet Health'),
(1, 'Creating an Enriching Environment for Your Cat', 'Cats need mental and physical stimulation to stay happy and healthy. Here are ways to create an enriching environment for your feline friend...\n\nVertical space: Cats love to climb and observe from high places. Cat trees, shelves, and perches give them the vertical territory they crave.\n\nHiding spots: Provide boxes, tunnels, or cat caves where your cat can retreat when they want privacy.\n\nInteractive toys: Toys that mimic prey movements engage your cat\'s hunting instincts. Rotate toys regularly to maintain interest.\n\nWindow views: Position perches near windows so your cat can watch birds, insects, and outdoor activities.\n\nScratch-friendly surfaces: Provide a variety of scratching posts with different textures and orientations (horizontal and vertical) to satisfy your cat\'s scratching needs.', 'img/blog-3.jpg', 'Cat Care');

-- Sample testimonials
INSERT INTO testimonials (name, title, content, avatar, rating, status) VALUES
('Sarah Ahmed', 'Cat Owner', 'PetStopBD helped me find my perfect feline companion. The adoption process was smooth, and their support afterward was excellent. I couldn\'t be happier with my new furry friend!', 'img/testimonial-1.jpg', 5, 'approved'),
('Karim Rahman', 'Volunteer', 'When I found an injured stray dog, I didn\'t know what to do. PetStopBD\'s rescue team guided me through the process and helped save the pup. They\'re doing amazing work for animals in Bangladesh.', 'img/testimonial-2.jpg', 5, 'approved'),
('Nusrat Jahan', 'Reptile Owner', 'The vet directory helped me find specialized care for my exotic pet. I finally found a vet who understands reptiles! The online pharmacy is also very convenient for regular medications.', 'img/testimonial-3.jpg', 4, 'approved');
