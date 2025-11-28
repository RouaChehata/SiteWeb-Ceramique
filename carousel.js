document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.carousel-image');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');
    const carousel = document.querySelector('.carousel-container');
    const dots = document.querySelectorAll('.carousel-dot');
    
    // Vérifier si les éléments existent
    if (!images.length || !prevBtn || !nextBtn || !carousel || !dots.length) {
        console.warn('Carousel elements not found');
        return;
    }
    
    let current = 0;
    let autoSlideInterval = null;

    function showImage(index) {
        images.forEach((img, i) => {
            img.classList.toggle('active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    function nextImage() {
        current = (current + 1) % images.length;
        showImage(current);
    }

    prevBtn.addEventListener('click', function() {
        current = (current - 1 + images.length) % images.length;
        showImage(current);
    });

    nextBtn.addEventListener('click', function() {
        nextImage();
    });

    carousel.addEventListener('mouseenter', function() {
        autoSlideInterval = setInterval(nextImage, 4000);
    });

    carousel.addEventListener('mouseleave', function() {
        clearInterval(autoSlideInterval);
    });

    dots.forEach((dot, i) => {
        dot.addEventListener('click', function() {
            current = i;
            showImage(current);
        });
    });

    showImage(current);
}); 