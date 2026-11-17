# Responsive page hero

Implemented on Repertoire (`/repertoire`) on 9 November 2026. Any Basic page with
an accessible Image Media item in **Hero image** uses the same component.

- Desktop/tablet at 768px and above: 18:5 ratio (1440 × 400 reference).
- Below 768px: 3:2 ratio.
- Core Responsive Image renders a decorative `<picture>` with focal-point image
  derivatives, eager loading, and high fetch priority. The image has empty alt
  text in this decorative placement; the Media item's own alt text is preserved.
- Black overlay opacity starts at 55%; tune against the final source/design.
- The node title is the hero H1; the usual title block is suppressed on hero
  pages. Pages without a hero retain the normal title/layout.
- The existing navigation overlays the hero and turns brand purple after 24px
  of scrolling. The mobile open menu also has a purple background. This is the
  scroll behaviour foundation; the final Figma navigation layout/CTA is separate.

## Editing

Edit the page and choose its Hero image through Media Library. Edit the Image
Media item and click the important point in the image preview to set its focal
point. The widget is enabled on both default and Media Library Image Media forms.
That focal point belongs to the image file and also affects other focal-point
styles using it. There is no separate mobile focal point.

## Files and configuration

- Theme `templates/components/page-hero.html.twig`, `templates/layout/page.html.twig`.
- Theme `deepcutsmusic11.theme`: hero render array and title handling.
- Theme `_hero.scss`, `_header.scss`, and `js/hero.js`.
- `scripts/setup-hero.php` creates image styles and the responsive mapping through
  Drupal APIs. Resulting configuration is exported to `sync/`.
- Composer installed Focal Point 2.1.3 and Crop 2.6.0. Core Responsive Image was
  already enabled.
- Mobile widths: 480, 768, 1536. Wide widths: 768, 1440, 2880. Responsive sources
  use `sizes="100vw"` and the existing theme breakpoints.

## Placeholder image

The designer has not supplied the final header images. At the user's direction,
Repertoire uses `_resources/Images/Homepage/Homepage intro header.jpg` (8192 × 5464)
as its placeholder. The requested `_resources/Homepage/` path resolves to this
existing Images subdirectory. Media 1 is labelled **Repertoire hero — homepage
image placeholder**. Its focal point is 45% horizontal / 35% vertical. This
replaces the earlier low-resolution acoustic thumbnail. Reassess crops and overlay
when the final header photo arrives. A mobile crop cannot keep every member
visible in all source compositions.

Media/file records, the focal point, and the node assignment are content, not
configuration export. Include them in database/content deployment.

## Verification

Site and editor Sass compile. Repertoire returns one H1, one responsive picture,
and the hero JS attachment. Desktop and 370px mobile layouts were inspected in
Safari. Both 1440 × 400 and 768 × 512 focal-point derivatives generate. Full
scroll/menu interaction testing and final-source visual comparison remain.
