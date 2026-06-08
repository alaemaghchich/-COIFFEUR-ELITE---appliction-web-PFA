<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section position-relative vh-100 d-flex align-items-center justify-content-center text-center">
    <div class="hero-fade-container">
        <img src="img/coifeurelite.jpeg" alt="Coiffeur Elite">
        <img src="img/coifeur.avif" alt="Coifeur">
        <img src="img/coifuerchair.jpeg" alt="Coifeur Chair">
    </div>
    <div class="hero-overlay"></div>
    
    <div class="hero-content position-relative z-index-2 px-3" style="z-index: 999;">
        <h1 class="display-1 fw-bold mb-4 animate__animated animate__fadeInDown">COIFFEUR ELITE</h1>
        <p class="lead text-light mb-5 fs-4 animate__animated animate__fadeInUp">PRECISION. LUXURY. STYLE</p>
        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
            <a href="#explore" class="btn btn-gold btn-lg px-5 py-3">Discover COIFFEUR ELITE</a>
            <a href="/customer/search.php" class="btn btn-outline-light btn-lg px-5 py-3">Book an Appointment</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="explore" class="py-5 bg-darker">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="mb-4">A New Standard in Hair Care</h2>
                <p class="text-gray-text lead">COIFFEUR ELITE is more than a booking site. It helps you find top barbers in the country.</p>
                <p class="text-gray-text">We choose our partners with care so each visit is high quality. From classic shaves to modern cuts, get the best service.</p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="fade-container ratio-16-9">
                            <img src="/img/salon.jpeg" alt="Salon">
                            <img src="/img/salon2.jpeg" alt="Salon 2">
                            <img src="/img/salon3.jpeg" alt="Salon 3">
                            <img src="/img/salon4.jpeg" alt="Salon 4">
                        </div>
                    </div>
                    <div class="col-6 mt-4">
                        <div class="fade-container ratio-9-16">
                            <img src="/img/haircut.avif" alt="Haircut">
                            <img src="/img/haircut1.jpeg" alt="Haircut 1">
                            <img src="/img/haircut2.jpeg" alt="Haircut 2">
                            <img src="/img/haircut3.jpeg" alt="Haircut 3">
                            <img src="/img/haircut4.jpeg" alt="Haircut 4">
                            <img src="/img/haircut5.jpeg" alt="Haircut 5">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Showcase -->
<script src="/assets/js/scroll.js"></script>
<section class="py-5">
    <div class="container py-5 text-center">
        <h2 class="mb-5">Our Top Services</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-luxury p-4 h-100">
                    <i class="fas fa-cut fa-3x text-gold mb-4"></i>
                    <h3>Haircut</h3>
                    <p class="text-gray-text">Precise cuts made for your face and style.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-luxury p-4 h-100">
                    <i class="fas fa-magic fa-3x text-gold mb-4"></i>
                    <h3>Beard Care</h3>
                    <p class="text-gray-text">Expert trimming with quality oils and balms.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-luxury p-4 h-100">
                    <i class="fas fa-spa fa-3x text-gold mb-4"></i>
                    <h3>Face Care</h3>
                    <p class="text-gray-text">Refreshing skin treatments for men.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Section -->
<section class="py-5" style="background-color: #000;">
    <div class="container py-5">
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-7">
                <h2 class="display-5 fw-bold mb-4 text-gold">Join Our Community</h2>
                <p class="lead text-light mb-4" style="opacity: 0.9;">Share your visit, rate barbers, and read real reviews. Our community helps you choose the right barber.</p>
                <div class="d-flex flex-wrap gap-4 mb-5 justify-content-center justify-content-md-start">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-comment-dots text-gold fa-2x me-3"></i>
                        <span class="text-white fs-5 fw-bold">Share Review</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-star text-warning fa-2x me-3"></i>
                        <span class="text-white fs-5 fw-bold">Rate Services</span>
                    </div>
                </div>
                <a href="/customer/search.php" class="btn btn-gold btn-lg px-5 py-3 shadow-lg fw-bold">
                    DISCOVER NOW <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            <div class="col-md-5 mt-5 mt-md-0">
                <div class="position-relative">
                    <div class="card-luxury p-4 shadow" style="transform: rotate(-3deg); background-color: #0A0A0A; border: 1px solid rgba(212, 175, 55, 0.5);">
                        <div class="d-flex align-items-center mb-3">
                            <img src="img/user.png" class="rounded-circle me-3" width="50">
                            <div>
                                <h6 class="mb-0 text-white">brahim medkour</h6>
                                <div class="text-gold small">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-white italic mb-0" style="font-style: italic;">"Best fade I've ever had! The attention to detail is unmatched. Definitely coming back."</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
