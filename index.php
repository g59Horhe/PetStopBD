<?php
// Include database connection
require_once 'config/db_connect.php';

// Fetch recent blog posts for the home page
$sql = "SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 3";
$result = $conn->query($sql);

// Fetch testimonials
$testimonialsSql = "SELECT * FROM testimonials ORDER BY id DESC LIMIT 3";
$testimonialsResult = $conn->query($testimonialsSql);

// Include header
include 'includes/header.php';
?>

<!-- Modern Hero Section -->
<section class="hero-modern">
    <div class="hero-background">
        <div class="hero-gradient"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-heart text-danger me-2"></i>
                        Bangladesh's #1 Pet Platform
                    </div>
                    <h1 class="hero-title">
                        Your <span class="text-gradient">One-Stop</span><br>
                        Pet Solution
                    </h1>
                    <p class="hero-subtitle">
                        Connect with pet lovers, find services, adopt companions, and create a better world for animals in Bangladesh.
                    </p>
                    <div class="hero-actions">
                        <a href="#" class="btn btn-modern btn-primary">
                            <i class="fas fa-paw me-2"></i>Find a Pet
                        </a>
                        <a href="#" class="btn btn-modern btn-outline">
                            <i class="fas fa-hand-holding-heart me-2"></i>Report Animal in Need
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Pets Rescued</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Adoptions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">5K+</div>
                            <div class="stat-label">Members</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <div class="hero-card floating">
                        <div class="card-content">
                            <i class="fas fa-heart text-danger fa-2x mb-3"></i>
                            <h5>Find Your Perfect Companion</h5>
                            <p class="text-muted small">Browse through hundreds of pets waiting for a loving home.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modern Services Section -->
<section class="services-modern py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <div class="section-badge">Our Services</div>
            <h2 class="section-title">Everything Your Pet Needs</h2>
            <p class="section-subtitle">Comprehensive care and services for every stage of your pet's life</p>
        </div>
        
        <div class="row g-4">
            <!-- Rescue Service -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-icon rescue">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <div class="service-content">
                        <h4>Pet Rescue</h4>
                        <p>Report animal cruelty, volunteer for rescue operations, and help reunite lost pets.</p>
                        <div class="service-features">
                            <span class="feature-tag">Emergency Response</span>
                            <span class="feature-tag">Lost & Found</span>
                        </div>
                        <a href="#" class="service-link">
                            Learn More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Owner Resources -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-icon owner">
                        <i class="fas fa-paw"></i>
                    </div>
                    <div class="service-content">
                        <h4>Owner Resources</h4>
                        <p>Find pet shops, services, and manage your pet's complete health and care profile.</p>
                        <div class="service-features">
                            <span class="feature-tag">Pet Profiles</span>
                            <span class="feature-tag">Services</span>
                        </div>
                        <a href="#" class="service-link">
                            Learn More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Adoption -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-icon adoption">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="service-content">
                        <h4>Pet Adoption</h4>
                        <p>Connect loving families with pets in need of homes through our trusted platform.</p>
                        <div class="service-features">
                            <span class="feature-tag">Verified Listings</span>
                            <span class="feature-tag">Safe Process</span>
                        </div>
                        <a href="#" class="service-link">
                            Learn More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Vet & Pharmacy -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-icon health">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="service-content">
                        <h4>Health & Care</h4>
                        <p>Access veterinary services and purchase medications through our online pharmacy.</p>
                        <div class="service-features">
                            <span class="feature-tag">Vet Directory</span>
                            <span class="feature-tag">Online Pharmacy</span>
                        </div>
                        <a href="#" class="service-link">
                            Learn More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Highlight -->
<section class="features-highlight py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="features-content">
                    <div class="section-badge">Why Choose PetStopBD</div>
                    <h2 class="section-title">Built for Pet Lovers, By Pet Lovers</h2>
                    <p class="section-subtitle">Our platform combines technology with compassion to create the best experience for pets and their humans.</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon-sm">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-text">
                                <h5>Verified & Safe</h5>
                                <p>All listings and users are verified for safety and authenticity.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon-sm">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="feature-text">
                                <h5>24/7 Support</h5>
                                <p>Round-the-clock emergency support for animal rescue and care.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon-sm">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="feature-text">
                                <h5>Community Driven</h5>
                                <p>Join thousands of pet lovers making a difference across Bangladesh.</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="#" class="btn btn-modern btn-primary mt-4">
                        Join Our Community <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="features-visual">
                    <div class="visual-card card-1">
                        <i class="fas fa-heart text-danger fa-2x mb-2"></i>
                        <h6>1000+ Rescues</h6>
                        <p class="small">Animals saved this year</p>
                    </div>
                    <div class="visual-card card-2">
                        <i class="fas fa-home text-success fa-2x mb-2"></i>
                        <h6>500+ Adoptions</h6>
                        <p class="small">Happy families created</p>
                    </div>
                    <div class="visual-card card-3">
                        <i class="fas fa-stethoscope text-primary fa-2x mb-2"></i>
                        <h6>150+ Vets</h6>
                        <p class="small">Healthcare providers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modern Blog Section -->
<section class="blog-modern py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <div class="section-badge">Latest Stories</div>
            <h2 class="section-title">Pet Care Insights</h2>
            <p class="section-subtitle">Expert advice, heartwarming stories, and essential tips for pet owners</p>
        </div>
        
        <div class="row g-4">
            <?php 
            // Check if we have blog posts in the database
            if ($result && $result->num_rows > 0) {
                $delay = 100;
                // Display blog posts from database
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <article class="blog-card-modern">
                            <div class="blog-image">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                                <div class="blog-category"><?php echo htmlspecialchars($row['category']); ?></div>
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span class="blog-date">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                    </span>
                                    <span class="blog-read-time">
                                        <i class="fas fa-clock me-1"></i>
                                        5 min read
                                    </span>
                                </div>
                                <h4 class="blog-title"><?php echo htmlspecialchars($row['title']); ?></h4>
                                <p class="blog-excerpt"><?php echo substr(htmlspecialchars($row['content']), 0, 120) . '...'; ?></p>
                                <a href="blog-post.php?id=<?php echo $row['id']; ?>" class="blog-link">
                                    Read More <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                    <?php
                    $delay += 100;
                }
            } else {
                // Display placeholder blog posts
                for ($i = 1; $i <= 3; $i++) {
                    $delay = $i * 100;
                    ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <article class="blog-card-modern">
                            <div class="blog-image">
                                <img src="img/blog-<?php echo $i; ?>.jpg" alt="Blog post image">
                                <div class="blog-category">Pet Care</div>
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span class="blog-date">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        May 15, 2025
                                    </span>
                                    <span class="blog-read-time">
                                        <i class="fas fa-clock me-1"></i>
                                        5 min read
                                    </span>
                                </div>
                                <h4 class="blog-title">How to Care for a Rescue Pet</h4>
                                <p class="blog-excerpt">Learn essential tips for helping your newly rescued pet adjust to their forever home and build trust...</p>
                                <a href="#" class="blog-link">
                                    Read More <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="#" class="btn btn-modern btn-outline">
                View All Articles <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Modern Testimonials -->
<section class="testimonials-modern py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <div class="section-badge">Success Stories</div>
            <h2 class="section-title">What Our Community Says</h2>
            <p class="section-subtitle">Real stories from pet lovers who found their perfect companions</p>
        </div>
        
        <div class="testimonials-slider">
            <div class="row g-4">
                <?php 
                // Check if we have testimonials in the database
                if ($testimonialsResult && $testimonialsResult->num_rows > 0) {
                    $delay = 100;
                    // Display testimonials from database
                    while($testimonial = $testimonialsResult->fetch_assoc()) {
                        ?>
                        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                            <div class="testimonial-card-modern">
                                <div class="testimonial-content">
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'active' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                                </div>
                                <div class="testimonial-author">
                                    <img src="<?php echo htmlspecialchars($testimonial['avatar']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="author-avatar">
                                    <div class="author-info">
                                        <h6 class="author-name"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                        <span class="author-title"><?php echo htmlspecialchars($testimonial['title']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $delay += 100;
                    }
                } else {
                    // Display placeholder testimonials
                    $testimonials = [
                        [
                            'name' => 'Sarah Ahmed',
                            'title' => 'Cat Owner',
                            'content' => 'PetStopBD helped me find my perfect feline companion. The adoption process was smooth, and their support afterward was excellent.',
                            'avatar' => 'img/testimonial-1.jpg',
                            'rating' => 5
                        ],
                        [
                            'name' => 'Karim Rahman',
                            'title' => 'Volunteer',
                            'content' => 'When I found an injured stray dog, PetStopBD\'s rescue team guided me through the process and helped save the pup.',
                            'avatar' => 'img/testimonial-2.jpg',
                            'rating' => 5
                        ],
                        [
                            'name' => 'Nusrat Jahan',
                            'title' => 'Reptile Owner',
                            'content' => 'The vet directory helped me find specialized care for my exotic pet. The online pharmacy is also very convenient.',
                            'avatar' => 'img/testimonial-3.jpg',
                            'rating' => 4
                        ]
                    ];
                    
                    $delay = 100;
                    foreach($testimonials as $testimonial) {
                        ?>
                        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                            <div class="testimonial-card-modern">
                                <div class="testimonial-content">
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'active' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                                </div>
                                <div class="testimonial-author">
                                    <img src="<?php echo htmlspecialchars($testimonial['avatar']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="author-avatar">
                                    <div class="author-info">
                                        <h6 class="author-name"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                        <span class="author-title"><?php echo htmlspecialchars($testimonial['title']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $delay += 100;
                    }
                }
                ?>
            </div>
        </div>
    </div>
</section>

<!-- Modern CTA Section -->
<section class="cta-modern py-5">
    <div class="container">
        <div class="cta-content text-center" data-aos="fade-up">
            <div class="cta-badge">
                <i class="fas fa-paw me-2"></i>
                Ready to Make a Difference?
            </div>
            <h2 class="cta-title">Join the PetStopBD Community</h2>
            <p class="cta-subtitle">
                Whether you're looking to adopt, help with rescue, or find the best care for your pet,<br>
                start your journey with Bangladesh's most trusted pet platform.
            </p>
            <div class="cta-actions">
                <a href="auth/register.php" class="btn btn-modern btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Sign Up Free
                </a>
                <a href="#" class="btn btn-modern btn-outline btn-lg">
                    <i class="fas fa-play me-2"></i>Watch Demo
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Add AOS (Animate On Scroll) library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
/* Modern CSS Variables */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    --glass-bg: rgba(255, 255, 255, 0.1);
    --glass-border: rgba(255, 255, 255, 0.2);
    --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.15);
    --shadow-medium: 0 15px 35px rgba(31, 38, 135, 0.2);
    --shadow-heavy: 0 25px 50px rgba(31, 38, 135, 0.25);
}

/* Modern Hero Section */
.hero-modern {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--primary-gradient);
}

.hero-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.7) 100%);
}

.floating-shapes {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
    animation: float 6s ease-in-out infinite;
}

.shape-1 {
    width: 200px;
    height: 200px;
    background: white;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.shape-2 {
    width: 150px;
    height: 150px;
    background: white;
    top: 60%;
    right: 10%;
    animation-delay: 2s;
}

.shape-3 {
    width: 100px;
    height: 100px;
    background: white;
    bottom: 20%;
    left: 20%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.min-vh-75 {
    min-height: 75vh;
}

.hero-content {
    color: white;
    z-index: 2;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 1.5rem;
}

.text-gradient {
    background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.25rem;
    line-height: 1.6;
    margin-bottom: 2.5rem;
    opacity: 0.9;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 3rem;
}

.btn-modern {
    padding: 12px 24px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.btn-modern.btn-primary {
    background: white;
    color: #667eea;
    box-shadow: var(--shadow-light);
}

.btn-modern.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    color: #667eea;
}

.btn-modern.btn-outline {
    background: transparent;
    color: white;
    border-color: white;
}

.btn-modern.btn-outline:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
}

.hero-stats {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.hero-stats .stat-item {
    text-align: center;
}

.hero-stats .stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.hero-stats .stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.hero-image {
    position: relative;
    z-index: 2;
}

.hero-card {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    backdrop-filter: blur(10px);
    color: white;
    max-width: 300px;
    margin: 0 auto;
}

.floating {
    animation: floatCard 3s ease-in-out infinite;
}

@keyframes floatCard {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Modern Services Section */
.services-modern {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
}

.section-header {
    margin-bottom: 4rem;
}

.section-badge {
    display: inline-block;
    padding: 6px 16px;
    background: var(--primary-gradient);
    color: white;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #2d3748;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #718096;
    max-width: 600px;
    margin: 0 auto;
}

.service-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    height: 100%;
    box-shadow: var(--shadow-light);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.8);
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-medium);
}

.service-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    margin-bottom: 1.5rem;
}

.service-icon.rescue {
    background: var(--secondary-gradient);
}

.service-icon.owner {
    background: var(--primary-gradient);
}

.service-icon.adoption {
    background: var(--success-gradient);
}

.service-icon.health {
    background: var(--warning-gradient);
}

.service-content h4 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #2d3748;
}

.service-content p {
    color: #718096;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.service-features {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.feature-tag {
    padding: 4px 12px;
    background: #f7fafc;
    border-radius: 20px;
    font-size: 0.8rem;
    color: #4a5568;
    border: 1px solid #e2e8f0;
}

.service-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.service-link:hover {
    color: #5a67d8;
    text-decoration: none;
}

/* Features Highlight */
.features-highlight {
    background: white;
}

.feature-list {
    margin: 2rem 0;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.feature-icon-sm {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 1rem;
    flex-shrink: 0;
}

.feature-text h5 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2d3748;
}

.feature-text p {
    color: #718096;
    margin: 0;
    font-size: 0.95rem;
}

.features-visual {
    position: relative;
    height: 400px;
}

.visual-card {
    position: absolute;
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: var(--shadow-light);
    width: 200px;
}

.visual-card.card-1 {
    top: 0;
    left: 0;
    animation: floatCard 3s ease-in-out infinite;
}

.visual-card.card-2 {
    top: 50%;
    right: 0;
    animation: floatCard 3s ease-in-out infinite;
    animation-delay: 1s;
}

.visual-card.card-3 {
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    animation: floatCard 3s ease-in-out infinite;
    animation-delay: 2s;
}

/* Modern Blog Section */
.blog-modern {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
}

.blog-card-modern {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-light);
    transition: all 0.3s ease;
    height: 100%;
}

.blog-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.blog-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.blog-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.blog-card-modern:hover .blog-image img {
    transform: scale(1.05);
}

.blog-category {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 4px 12px;
    background: var(--primary-gradient);
    color: white;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.blog-content {
    padding: 1.5rem;
}

.blog-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: #718096;
}

.blog-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #2d3748;
    line-height: 1.4;
}

.blog-excerpt {
    color: #718096;
    margin-bottom: 1rem;
    line-height: 1.6;
    font-size: 0.95rem;
}

.blog-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.blog-link:hover {
    color: #5a67d8;
    text-decoration: none;
}

/* Modern Testimonials */
.testimonials-modern {
    background: white;
}

.testimonial-card-modern {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-light);
    border: 1px solid #f7fafc;
    height: 100%;
    transition: all 0.3s ease;
}

.testimonial-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.rating {
    margin-bottom: 1rem;
}

.rating i {
    color: #e2e8f0;
    margin-right: 2px;
}

.rating i.active {
    color: #fbb040;
}

.testimonial-text {
    font-size: 1rem;
    line-height: 1.6;
    color: #4a5568;
    margin-bottom: 1.5rem;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
}

.author-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 1rem;
    object-fit: cover;
}

.author-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.author-title {
    color: #718096;
    font-size: 0.9rem;
}

/* Modern CTA Section */
.cta-modern {
    background: var(--primary-gradient);
    color: white;
}

.cta-content {
    max-width: 800px;
    margin: 0 auto;
}

.cta-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.cta-subtitle {
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2.5rem;
    opacity: 0.9;
}

.cta-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 992px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .cta-title {
        font-size: 2rem;
    }
    
    .hero-actions,
    .cta-actions {
        justify-content: center;
        text-align: center;
    }
    
    .features-visual {
        margin-top: 3rem;
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .visual-card {
        width: 160px;
        padding: 1rem;
    }
    
    .feature-item {
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Initialize AOS
AOS.init({
    duration: 1000,
    once: true,
    offset: 100
});

// Counter animation for stats
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                clearInterval(timer);
                counter.textContent = counter.textContent.replace(/\d+/, target);
            } else {
                const value = Math.floor(current);
                counter.textContent = counter.textContent.replace(/\d+/, value);
            }
        }, 16);
    });
}

// Trigger counter animation when stats come into view
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            observer.unobserve(entry.target);
        }
    });
});

const statsSection = document.querySelector('.hero-stats');
if (statsSection) {
    observer.observe(statsSection);
}
</script>

<?php
// Include footer
include 'includes/footer.php';

// Close the database connection
$conn->close();
?>