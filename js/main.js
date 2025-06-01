// Main JavaScript for PetStopBD

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize any components that need JavaScript
    
    // Statistics counter animation
    const statElements = document.querySelectorAll('.stat-number');
    
    if (statElements.length > 0) {
        // Function to animate counting
        function animateCounter(element, target) {
            let count = 0;
            const duration = 2000; // 2 seconds
            const interval = 50; // Update every 50ms
            const steps = duration / interval;
            const increment = target / steps;
            
            const timer = setInterval(() => {
                count += increment;
                if (count >= target) {
                    clearInterval(timer);
                    element.textContent = target.toLocaleString();
                } else {
                    element.textContent = Math.floor(count).toLocaleString();
                }
            }, interval);
        }
        
        // Function to check if element is in viewport
        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
        
        // Initialize Intersection Observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.getAttribute('data-target'));
                    animateCounter(entry.target, target);
                    observer.unobserve(entry.target); // Only animate once
                }
            });
        }, { threshold: 0.1 });
        
        // Observe each stat element
        statElements.forEach(stat => {
            observer.observe(stat);
        });
    }
    
    // Testimonial carousel (if using)
    const testimonialCarousel = document.getElementById('testimonialCarousel');
    if (testimonialCarousel) {
        const carousel = new bootstrap.Carousel(testimonialCarousel, {
            interval: 5000, // Change slide every 5 seconds
            wrap: true
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add active class to nav items based on current page
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (linkPath && currentLocation.includes(linkPath) && linkPath !== '#') {
            link.classList.add('active');
        }
    });
});
