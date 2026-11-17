# Page sections with Paragraphs

Implemented 10 November 2026. Paragraphs 1.23.0 and Entity Reference Revisions
1.15.0 are installed through Composer. Existing core Views, Media Library, Link,
Options, Text, and taxonomy modules supply the remaining functionality. No
additional layout-builder or carousel package is required.

## Authoring a page

Edit a Basic page and use **Page sections** to add, edit, and reorder components.
Sections belong to their parent page and use revision-aware references. The
existing Body remains available for simple pages; when Page sections is nonempty,
the full-page template renders sections instead of Body. Body data is preserved.
Policies and other pages without sections retain their existing output.

Every section supports an optional heading, Standard/Narrow content width, and a
controlled background palette: White, Pale lavender, Lavender, Pale gold, Brand
purple, or Black. Brand/dark variants use light text.

| Paragraph type | Fields and behaviour |
| --- | --- |
| Text (`text`) | Formatted text; one/two/three desktop columns; optional border for introductory panels. Columns collapse on smaller screens. |
| Image and text (`image_text`) | Required Image Media; formatted text; image left/right on desktop; optional labelled button. Stacks image then copy on mobile. |
| Card group (`card_group`) | Ordered references to Package, Add-on, Band member, or Article nodes; one/two/three desktop columns. Card text/images come from the referenced content. |
| Reviews (`reviews`) | Ordered Review references, or published featured reviews if left empty; Grid or Swipeable carousel. Carousel uses native scrolling, scroll snap, and progressive Previous/Next buttons, without autoplay. |
| Page breaker (`page_breaker`) | Heading, optional formatted text and labelled button; background extends across the page. |
| Content listing (`content_listing`) | Controlled selection of Songs, FAQs, Packages, Add-ons, Band members, or Videos. Song listings may be filtered by one repertoire category. |
| Video (`video`) | One Remote video or uploaded Video Media with an optional heading; add/reorder sections freely. See [VIDEOS.md](VIDEOS.md). |

Buttons require their link text. Image fields accept Image Media only. Selected
content is rendered subject to entity access checks. Views filter unpublished
content. No editor roles or permissions were changed; the administrator can edit
sections, and any later editorial roles will need appropriate Paragraphs and
text-format permissions.

## Repertoire

Repertoire now has six sections matching the current Figma design:

1. Bordered Text introduction: **Chart-toppers and deep cuts**.
2. Songs listing: **Modern bangers**, 27 songs, pale lavender.
3. Songs listing: **90s & 00s hits**, 48 songs, lavender.
4. Songs listing: **Vintage & Classics**, 46 songs, pale lavender.
5. Songs listing: **Acoustic vibes**, 50 songs, lavender.
6. Pale-lavender Page breaker: **“Do you know how to play...?”**.

The 171 Song nodes and four taxonomy terms were transcribed from the Figma Sites
preview. Titles, artist spellings, categorisation, and ordering are preserved as
supplied, including any source typos; no repertoire facts were independently
changed. Edit individual songs under Content; lower **Display order** sorts first,
with node ID as a stable tie-breaker. Page section order controls category order.

The closing section follows the supplied design's copy and has no extra booking
button. Editors can add one using its optional Button field if desired.
The existing homepage-image hero placeholder remains. No new header or footer
elements, templates, or styling were created in this change.

## Implementation

- Paragraph theming: `themes/custom/deepcutsmusic11/scss/_paragraphs.scss`, imported
  by `style.scss` and compiled into site/CKEditor CSS.
- Shared section template: `templates/paragraph/paragraph.html.twig`.
- Basic page section output: `templates/content/node--page--full.html.twig`.
- Referenced content: `dc_card` node view mode and `node--dc-card.html.twig`, with
  specialised Song, FAQ, and Review markup.
- Theme preprocessing builds controlled Views and selected-entity render arrays,
  preserving entity access/cache metadata. No arbitrary View IDs can be entered.
- `js/paragraphs.js` adds review carousel controls and respects reduced motion.
- Views: `deepcuts_song`, `deepcuts_faq`, `deepcuts_package`, `deepcuts_add_on`,
  `deepcuts_band_member`, `deepcuts_review`, and `deepcuts_video`.

## Reproduction and deployment

`scripts/build-paragraphs.php` records construction of fields, displays, Views,
and Repertoire content. `scripts/data/repertoire.json` contains the supplied song
data. Run after the initial content model/pages exist:

```sh
ddev composer install
ddev drush en paragraphs -y
ddev drush php:script scripts/build-paragraphs.php
ddev drush cr
```

The script preserves existing matching songs/terms and does not replace a
Repertoire page that already has sections. It is an initial-build utility rather
than a migration for later editorial changes. It does not import the hero image.

Full active configuration is exported to `sync/`. Paragraph instances, Songs,
terms, and node assignments are database content and are not included in that
export. Deploy content via the database/content migration workflow as well as
Composer and configuration import.

## Verification

- All six paragraph editing forms and the Basic page Paragraphs widget build.
- Page/paragraph entity validation passes under the administrator account; text
  format access means the anonymous CLI account cannot validate `basic_html` input.
- All seven Views execute; category filters return 27/48/46/50 Song rows.
- Anonymous Repertoire output returns HTTP 200, six sections, and 171 Song rows.
- Site/editor SCSS compiles and PHP lint passes.
- Desktop and 370px mobile rendering was inspected in Safari, including long
  song titles wrapping within the tinted list.
- `scripts/verify-paragraphs.php` renders every section variant and checks featured
  and manually selected review publication filtering using rolled-back fixtures.
  Clear caches after running it. No test reviews/packages remain in the database.
- Carousel touch/button interactions and the other component layouts still need
  visual review with real content as those pages are built.
