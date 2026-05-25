document.addEventListener('DOMContentLoaded', () => {
    // --- Standard Fade Galleries (About Section) ---
    const fadeContainers = document.querySelectorAll('.fade-container');
    fadeContainers.forEach(container => {
        const images = container.querySelectorAll('img');
        if (images.length === 0) return;
        let currentIndex = 0;
        images[0].classList.add('active');
        setInterval(() => {
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        }, 4000);
    });

    // --- Hero Fade Gallery ---
    const heroContainer = document.querySelector('.hero-fade-container');
    if (heroContainer) {
        const heroImages = heroContainer.querySelectorAll('img');
        if (heroImages.length > 0) {
            let heroIndex = 0;
            heroImages[0].classList.add('active');
            setInterval(() => {
                heroImages[heroIndex].classList.remove('active');
                heroIndex = (heroIndex + 1) % heroImages.length;
                heroImages[heroIndex].classList.add('active');
            }, 5000); // Hero stays a bit longer
        }
    }
});
