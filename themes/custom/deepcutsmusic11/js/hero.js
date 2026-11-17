(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.deepcutsHeroHeader = {
    attach(context) {
      once('deepcuts-hero-header', '[data-hero-header]', context).forEach((header) => {
        const update = () => header.classList.toggle('is-scrolled', window.scrollY > 24);
        window.addEventListener('scroll', update, { passive: true });
        update();
      });
    }
  };
})(Drupal, once);
