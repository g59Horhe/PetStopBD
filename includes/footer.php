</main>
    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">
                        <span class="text-primary">PetStop</span><span class="text-danger">BD</span>
                    </h6>
                    <p>
                        Your one stop pet solution - providing a comprehensive platform for animal lovers, pet owners,
                        rescuers, and adopters in Bangladesh.
                    </p>
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Services</h6>
                    <p><a href="#" class="text-reset">Pet Rescue</a></p>
                    <p><a href="#" class="text-reset">Pet Adoption</a></p>
                    <p><a href="#" class="text-reset">Vet Services</a></p>
                    <p><a href="#" class="text-reset">Pet Supplies</a></p>
                </div>

                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Useful links</h6>
                    <p><a href="#" class="text-reset">About Us</a></p>
                    <p><a href="#" class="text-reset">Terms of Service</a></p>
                    <p><a href="#" class="text-reset">Privacy Policy</a></p>
                    <p><a href="#" class="text-reset">Contact</a></p>
                </div>

                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Contact</h6>
                    <p><i class="fas fa-home me-3"></i> Dhaka, Bangladesh</p>
                    <p><i class="fas fa-envelope me-3"></i> info@petstopbd.com</p>
                    <p><i class="fas fa-phone me-3"></i> +880 1XXX XXXXXX</p>
                    <div class="mt-4">
                        <a href="#" class="me-2 text-reset"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2 text-reset"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2 text-reset"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center p-4" style="background-color: rgba(0, 0, 0, 0.05);">
            © <?php echo date("Y"); ?> PetStopBD. All rights reserved.
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo dirname($_SERVER['PHP_SELF']) == '/petstopbd' ? 'js/main.js' : '../js/main.js'; ?>"></script>
</body>
</html>
