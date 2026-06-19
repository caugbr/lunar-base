
/*
   ============================================
   LUNAR APPS - Star Field Generator
   Call addStars(num) from your page
   ============================================
*/
function addStars(num = 25) {
    const container = document.querySelector('.hero-image-wrapper');
    if (!container) return;

    // Remove any existing stars
    const existingStars = container.querySelectorAll('.star-decoration');
    existingStars.forEach(star => star.remove());

    // Generate stars
    for (let i = 0; i < num; i++) {
        const star = document.createElement('span');
        star.className = 'star-decoration';
        star.setAttribute('aria-hidden', 'true');

        // Random position (0-100%)
        const top = Math.random() * 100;
        const left = Math.random() * 100;

        // Random size (1px to 5px)
        const size = 1 + Math.random() * 4;

        // Random delay (0 to 20s, matching moon cycle)
        const delay = Math.random() * 20;

        // Random animation duration variation (18s to 22s)
        const duration = 18 + Math.random() * 4;

        // Apply styles with dynamic glow
        star.style.cssText = `
            top: ${top}%;
            left: ${left}%;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            box-shadow: 0 0 ${size * 2}px var(--hero-star-shadow);
            animation: twinkle ${duration}s ease-in-out infinite;
            animation-delay: ${delay}s;
        `;

        container.appendChild(star);
    }
}
