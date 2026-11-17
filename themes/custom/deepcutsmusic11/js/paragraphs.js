(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.deepcutsQuotes = {
    attach(context) {
      once('deepcuts-quotes', '[data-quotes-carousel]', context).forEach((section) => {
        const track = section.querySelector('.view-content') || section.querySelector('.dc-section__items');
        if (!track || track.children.length < 2) return;
        const controls = section.querySelector('.dc-section__controls');
        controls.hidden = false;
        controls.querySelectorAll('button').forEach((button) => {
          button.addEventListener('click', () => track.scrollBy({
            left: Number(button.dataset.quoteDirection) * track.clientWidth,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'
          }));
        });
      });
    }
  };
})(Drupal, once);
