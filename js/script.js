let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-image');
const totalSlides = slides.length;

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.classList.remove('active');
        if (i === index) {
            slide.classList.add('active');
        }
    });
}

function changeSlide(direction) {
    currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
    showSlide(currentSlide);
}

function nextSlide() {
    changeSlide(1);
}

// Show the first slide initially
showSlide(currentSlide);

// Automatically change slides every 5 seconds
setInterval(nextSlide, 5000);
