/**
 * Big Hero Module - Optional JavaScript
 * Parallax effect on scroll (subtle)
 */
(function() {
    'use strict';

    function initBigHeroParallax() {
        const bigHeroes = document.querySelectorAll('.big-hero');
        
        if (!bigHeroes.length) return;

        // Check for reduced motion preference
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) return;

        bigHeroes.forEach(function(hero) {
            const bgMedia = hero.querySelector('.big-hero__bg-media');
            if (!bgMedia) return;

            // Subtle parallax on scroll
            let ticking = false;

            function updateParallax() {
                const rect = hero.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                
                // Only apply parallax when hero is in view
                if (rect.bottom < 0 || rect.top > viewportHeight) {
                    ticking = false;
                    return;
                }

                // Calculate parallax offset (subtle movement)
                const scrolled = rect.top / viewportHeight;
                const yPos = scrolled * 30; // 30px max movement

                bgMedia.style.transform = 'translate(-50%, calc(-50% + ' + yPos + 'px))';
                ticking = false;
            }

            function onScroll() {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }

            // Only enable for video backgrounds (images use object-fit: cover)
            if (bgMedia.tagName === 'VIDEO') {
                window.addEventListener('scroll', onScroll, { passive: true });
                updateParallax(); // Initial call
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBigHeroParallax);
    } else {
        initBigHeroParallax();
    }

    // Re-initialize on AJAX content load (for dynamic content)
    document.addEventListener('ikigai:content-loaded', initBigHeroParallax);

})();
