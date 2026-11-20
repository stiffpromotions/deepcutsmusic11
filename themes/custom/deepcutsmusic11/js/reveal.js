(function (Drupal, once) {
  'use strict';

  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const selector = '.page-sections .dc-section, .dc-home-card, .dc-home-reviews, .site-footer';

  Drupal.behaviors.deepcutsReveal = {
    attach(context) {
      if (!('IntersectionObserver' in window) || motion.matches) return;

      once('deepcuts-reveal', selector, context).forEach((section) => {
        // Reveal listing cards separately; avoid fading both a parent and its children.
        const rows = section.querySelectorAll('.view-content > .views-row');
        const targets = section.matches('.dc-section--content-listing') && rows.length
          ? [...section.querySelectorAll('.dc-section__inner > .dc-section__heading, .dc-section__inner > .dc-section__copy'), ...rows]
          : [section];

        const observer = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-revealed');
            }
            else if (!entry.isIntersecting) {
              // Reset offscreen without an exit animation, ready for another entrance.
              entry.target.classList.remove('is-revealed');
            }
          });
        }, { threshold: 0 });

        targets.forEach((target) => {
          target.classList.add('dc-reveal');
          observer.observe(target);
          // Keyboard focus must never land on invisible content.
          target.addEventListener('focusin', () => target.classList.add('is-revealed'));
        });

        motion.addEventListener('change', (event) => {
          if (event.matches) {
            observer.disconnect();
            targets.forEach((target) => target.classList.remove('dc-reveal'));
          }
        });
      });
    }
  };
})(Drupal, once);
