(function (Drupal, once) {
  'use strict';
  let youtubeReady;
  const loadYouTube = () => {
    if (window.YT && window.YT.Player) return Promise.resolve();
    if (!youtubeReady) youtubeReady = new Promise((resolve, reject) => {
      const previous = window.onYouTubeIframeAPIReady;
      window.onYouTubeIframeAPIReady = () => { if (previous) previous(); resolve(); };
      const script = document.createElement('script');
      script.src = 'https://www.youtube.com/iframe_api';
      script.onerror = reject;
      document.head.appendChild(script);
    });
    return youtubeReady;
  };
  Drupal.behaviors.deepcutsHome = {
    attach(context) {
      once('dc-home-video', '[data-home-video]', context).forEach(async (hero) => {
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const mute = hero.querySelector('[data-video-mute]');
        const play = hero.querySelector('[data-video-play]');
        let player;
        try {
          await loadYouTube();
          player = new window.YT.Player(hero.querySelector('[data-youtube-player]'), {
            host: 'https://www.youtube-nocookie.com',
            videoId: hero.dataset.videoId,
            playerVars: { autoplay: reduced.matches ? 0 : 1, mute: 1, controls: 0, playsinline: 1, loop: 1, playlist: hero.dataset.videoId, rel: 0, disablekb: 1, origin: window.location.origin },
            events: {
              onReady(event) {
                event.target.mute();
                const iframe = event.target.getIframe();
                iframe.title = Drupal.t('Deep Cuts live performance');
                iframe.tabIndex = -1;
                iframe.setAttribute('aria-hidden', 'true');
                iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
                new ResizeObserver(() => {
                  const box = hero.getBoundingClientRect();
                  const width = Math.max(box.width, box.height * 16 / 9);
                  iframe.style.width = `${width}px`;
                  iframe.style.height = `${width * 9 / 16}px`;
                }).observe(hero);
                hero.classList.add('is-ready'); mute.hidden = false; play.hidden = false;
                if (!reduced.matches) event.target.playVideo();
              },
              onStateChange(event) {
                const playing = event.data === window.YT.PlayerState.PLAYING;
                if (playing) hero.classList.add('is-playing');
                play.textContent = playing ? Drupal.t('Pause video') : Drupal.t('Play video');
              },
              onError() { hero.classList.remove('is-ready', 'is-playing'); mute.hidden = true; play.hidden = true; }
            }
          });
          mute.addEventListener('click', () => {
            if (player.isMuted()) { player.unMute(); mute.textContent = Drupal.t('Mute'); }
            else { player.mute(); mute.textContent = Drupal.t('Unmute'); }
          });
          play.addEventListener('click', () => {
            if (player.getPlayerState() === window.YT.PlayerState.PLAYING) player.pauseVideo();
            else player.playVideo();
          });
          reduced.addEventListener('change', () => { if (reduced.matches && player.pauseVideo) player.pauseVideo(); });
        }
        catch (_) { /* Keep the poster and ordinary YouTube link usable. */ }
      });
      once('dc-logo-carousel', '[data-logo-carousel]', context).forEach((section) => {
        const viewport = section.querySelector('.dc-logos__viewport');
        const rows = [...section.querySelectorAll('.views-row')];
        const track = section.querySelector('.view-content');
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        let index = 0, perPage = 3, timer, transitionTimer;
        const batches = () => Math.ceil(rows.length / perPage);
        const position = (animate = false) => {
          track.style.transition = animate ? 'transform 700ms ease-in-out' : 'none';
          track.style.transform = `translateX(-${index * 100}%)`;
          rows.forEach((row, i) => row.setAttribute('aria-hidden', Math.floor(i / perPage) === index ? 'false' : 'true'));
        };
        const schedule = () => {
          clearTimeout(timer);
          if (!reduced.matches && !document.hidden && !section.matches(':hover') && !section.contains(document.activeElement) && batches() > 1) {
            timer = setTimeout(() => {
              index++; position(true);
              transitionTimer = setTimeout(() => {
                // A duplicate first batch makes the wrap move in the same direction.
                if (index === batches()) { index = 0; position(); }
                schedule();
              }, 700);
            }, 5000);
          }
        };
        const resize = () => {
          clearTimeout(timer); clearTimeout(transitionTimer);
          track.querySelectorAll('[data-logo-spacer], [data-logo-clone]').forEach((item) => item.remove());
          section.classList.toggle('is-enhanced', !reduced.matches);
          perPage = parseInt(getComputedStyle(section).getPropertyValue('--logos-per-page'), 10);
          index = 0;
          if (reduced.matches) {
            track.style.transition = 'none'; track.style.transform = 'none';
            rows.forEach((row) => row.removeAttribute('aria-hidden'));
            return;
          }
          const appendClone = (row) => {
            const clone = row.cloneNode(true); clone.dataset.logoClone = ''; clone.setAttribute('aria-hidden', 'true'); clone.inert = true;
            clone.removeAttribute('id'); clone.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));
            track.appendChild(clone);
          };
          // Fill the final batch from the start of the list instead of leaving
          // empty slots. Modulo also handles fewer logos than a full viewport.
          for (let i = rows.length; i < batches() * perPage; i++) {
            appendClone(rows[(i - rows.length) % rows.length]);
          }
          // Append a complete first batch for the seamless forward wrap.
          if (batches() > 1) {
            for (let i = 0; i < perPage; i++) appendClone(rows[i % rows.length]);
          }
          viewport.scrollLeft = 0; position(); schedule();
        };
        if (!rows.length) return;
        ['mouseenter', 'mouseleave', 'focusin'].forEach((event) => section.addEventListener(event, schedule));
        section.addEventListener('focusout', () => setTimeout(schedule, 0));
        document.addEventListener('visibilitychange', schedule);
        reduced.addEventListener('change', resize);
        new ResizeObserver(resize).observe(viewport); resize();
      });
      once('dc-home-reviews', '[data-home-reviews]', context).forEach((section) => {
        const rows = [...section.querySelectorAll('.views-row')];
        const dots = section.querySelector('[data-review-dots]');
        const status = section.querySelector('[data-review-status]');
        let index = 0;
        const show = (next, announce = true) => {
          index = (next + rows.length) % rows.length;
          rows.forEach((row, i) => {
            row.hidden = false;
            row.classList.toggle('is-active', i === index);
            row.setAttribute('aria-hidden', i === index ? 'false' : 'true');
            row.inert = i !== index;
          });
          [...dots.children].forEach((button, i) => button.setAttribute('aria-current', i === index ? 'true' : 'false'));
          if (announce) status.textContent = Drupal.t('Review @current of @total', { '@current': index + 1, '@total': rows.length });
        };
        if (rows.length < 2) return;
        rows.forEach((row, i) => {
          const dot = document.createElement('button'); dot.type = 'button';
          dot.setAttribute('aria-label', Drupal.t('Show review @number', { '@number': i + 1 }));
          dot.addEventListener('click', () => show(i)); dots.appendChild(dot);
        });
        section.querySelector('[data-review-prev]').addEventListener('click', () => show(index - 1));
        section.querySelector('[data-review-next]').addEventListener('click', () => show(index + 1));
        section.querySelector('.dc-home-reviews__controls').hidden = false;
        show(0, false);
        section.classList.add('is-enhanced');
      });
    }
  };
})(Drupal, once);
