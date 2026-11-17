# Homepage

Implemented from the Home frame in Deep Cuts WIP on 17 November 2026.

## Editing

Edit **Home** (`/node/1/edit`) to change the introduction and its image/button,
reorder the two groups of three feature cards, or change each card's heading,
image, text and destination. Event cards currently link to Book now. The lower
cards link to Packages, Testimonials and Videos. All seven supplied homepage
images are reusable Image Media items named `Home: …`.

The **Hero video** references the existing **Media 10**. Edit that Media item or
select another YouTube Remote video on Home to replace it. The player autoplays
muted, loops and provides Mute/Unmute plus Pause/Play controls. Navigation overlays
the video and becomes purple on scroll or when the mobile menu opens. Reduced
motion disables autoplay; a poster and ordinary YouTube link remain available if
the player cannot load. YouTube/browser policy may prevent autoplay on some devices.
The implementation currently supports YouTube Remote video Media for this hero.

Add company logos through **Content → Add content → Logo** (`/node/add/logo`).
The type has Title and a PNG image field; provide the company name as alt text.
Unpublish or delete a Logo to remove it. Editors can create, edit and delete Logos.
The `Deep Cuts: homepage logos` View orders published logos by Title. The Logo
image style applies Color Shift `#444444`, Desaturate, then Scale within 80 × 80
without enlarging small sources, using Image Effects. Royal Navy's JPEG source was
converted to PNG during import. All 14 supplied logos have been imported.

Logos hold each full visible batch for **five seconds**, then slide smoothly to
the next batch, including a seamless forward loop to the beginning. The last batch fills any remaining slots with logos from the beginning, so every
batch stays full at every breakpoint, even when the logo count is not a multiple
of the number visible. Counts are three on
phones, four from 576px, six from 768px and eight from 992px. There are no manual logo controls. Hover, keyboard focus and a hidden browser
tab pause the timer; reduced motion displays a static, manually scrollable strip. Without JavaScript, the strip
can be scrolled manually.

For reviews, edit a **Review** and use **Promotion options → Promoted to front
page** to include it in the random pool. The Homepage reviews section on Home
selects the first quote (the first selected Review is used). It currently pins
the Figma quote, Review 187, then displays four distinct, randomly ordered,
published promoted reviews. All eight existing published reviews were initially
promoted. Changes to promotion status are preserved on subsequent setup runs.
At least four other published promoted reviews are needed to keep five slides.
The carousel is manual, with arrows, five position buttons and a screen-reader
announcement. Quotes crossfade over 350ms and share a grid cell so the tallest
review determines the panel height for every slide. This automatically adapts to
viewport and font changes. Reduced motion removes the fade. Review notes crediting a previous band are retained.

`deepcuts_home` alters only the `deepcuts_home_reviews` View to pin the selected
Review and randomise the rest. The View has no result cache and Home bypasses
page cache so each page request can obtain a fresh selection. Other pages keep
their existing caching and review Views. Logos, like the other reusable supporting
content, do not have public standalone pages or sitemap entries.

## Implementation and deployment

- Theme: `scss/_home.scss`, `js/home.js`, homepage component/paragraph templates,
  and homepage branches of the page/paragraph preprocess functions.
- Exported configuration: Logo type/field/style, nested feature-card paragraphs,
  the two homepage Views, Page section choices and Editor role permissions.
- Dependencies: `drupal/image_effects` and its Composer dependencies, plus custom
  `deepcuts_home` and existing `deepcuts_content`.
- Database content and public files must be transferred along with configuration;
  configuration import alone does not transfer nodes, Media or Paragraphs.

For this existing project/database, setup can be repeated with:

```sh
ddev drush en image_effects deepcuts_home -y
ddev drush php:script scripts/build-home-page.php
ddev drush cr
ddev drush php:script scripts/verify-home-page.php
```

The setup utility preserves existing Home sections, card edits, Media, logos and
review promotion choices on reruns. It relies on this project's existing Home
node 1, Media 10 and pinned Review 187; it is not a generic fresh-site installer.
These scripts are development/setup tools and are not invoked by public requests.

Verified the complete page at desktop and 390px phone width, muted playback and
both audio labels, manual review navigation, responsive logo batches, complete
card copy, View uniqueness/pinning/randomisation, image effects and Editor
permissions. SCSS builds for both site and CKEditor pass.
