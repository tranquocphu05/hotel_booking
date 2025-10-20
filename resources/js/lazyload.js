/**
 * Lazy Loading Images for Performance Optimization
 * Automatically loads images when they enter viewport
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all images with data-src attribute
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    // Check if Intersection Observer is supported
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Replace src with data-src
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                    }
                    
                    // Replace srcset if exists
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                    }
                    
                    // Add loaded class for fade-in effect
                    img.classList.add('lazy-loaded');
                    
                    // Remove data attributes after loading
                    img.removeAttribute('data-src');
                    img.removeAttribute('data-srcset');
                    
                    // Stop observing this image
                    observer.unobserve(img);
                }
            });
        }, {
            // Load images 50px before they enter viewport
            rootMargin: '50px 0px',
            threshold: 0.01
        });
        
        // Observe all lazy images
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers that don't support Intersection Observer
        lazyImages.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
            }
        });
    }
    
    // Lazy load background images
    const lazyBackgrounds = document.querySelectorAll('[data-bg]');
    
    if ('IntersectionObserver' in window && lazyBackgrounds.length > 0) {
        const bgObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    element.style.backgroundImage = `url(${element.dataset.bg})`;
                    element.classList.add('lazy-loaded');
                    element.removeAttribute('data-bg');
                    observer.unobserve(element);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });
        
        lazyBackgrounds.forEach(bg => {
            bgObserver.observe(bg);
        });
    }
});

// Optimize images on load with progressive enhancement
window.addEventListener('load', function() {
    // Add WebP support detection
    const supportsWebP = document.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') === 0;
    
    if (supportsWebP) {
        document.documentElement.classList.add('webp');
    } else {
        document.documentElement.classList.add('no-webp');
    }
});

