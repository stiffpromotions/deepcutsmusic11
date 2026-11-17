# Content model

## Initial page structure

Created 9 November 2026 as published Basic page nodes with brief placeholder
copy, ready for individual page builds:

| Page | Node | URL |
| --- | --- | --- |
| Home | 1 | `/` (also `/home`) |
| About | 2 | `/about` |
| Packages | 3 | `/packages` |
| Videos | 4 | `/videos` |
| Repertoire | 5 | `/repertoire` |
| Testimonials | 6 | `/testimonials` |
| FAQs | 7 | `/faqs` |
| Book now | 8 | `/book-now` |
| Privacy policy | 9 | `/privacy-policy` |
| Cookie policy | 10 | `/cookie-policy` |
| Thank you | 11 | `/thank-you` |
| Mic drop… (404) | 12 | `/error` |

Main menu links point to the first eight pages, using the existing core Home link.
Privacy and Cookie policy links are in the Footer menu. Thank you and Error are
not in the menus. `system.site` assigns node 1 as the front page and node 12 as
the 404 page. The contact form, final policy text, media, Views, and page-specific
components are not implemented yet. The thank-you page does not yet receive a
form redirect. Structured items such as Reviews, Songs, and Packages will be
authored separately and listed within these landing pages.

Nodes, aliases, and custom menu links are database content and are **not** included
in configuration export. `scripts/build-site-pages.php` records initial creation
and skips existing matching pages. On a fresh target, create/import content before
using front/404 settings that reference node IDs; IDs can differ between sites.
Use a database/content migration for subsequent deployment of edited page content.

## Field definitions

Implemented 9 November 2026. All content types require a title. An asterisk below
marks other required fields. New content types create revisions by default and
do not display author/date metadata or promote themselves to the homepage.

| Type (machine name) | Fields beyond title |
| --- | --- |
| Article (`article`) | Body with summary; Image (one Image Media reference) |
| Basic page (`page`) | Body with summary; Subtitle; Hero image (Image Media); Hero video (local Video or Remote video Media) |
| Review (`review`) | Review quote* (formatted long text); Reviewer display name*; Event type; Event date (date only); Review source (external link, optional label); Featured on homepage (default off); Display order (default 0) |
| FAQ (`faq`) | Answer* (formatted long text); Category; Display order |
| Band member (`band_member`) | Biography* with summary; Role / instrument*; Portrait (Image Media); Display order |
| Package (`package`) | Description* with summary; Inclusions* (formatted long text); Image (Image Media); Price wording; Available add-ons (multiple Add-on references); Display order |
| Add-on (`add_on`) | Description* with summary; Image (Image Media); Price wording; Display order |
| Song (`song`) | Original artist*; Repertoire category (multiple terms); Display order |

Review's title is labelled “Review title (editorial label)”; testimonial Views
should render the quote and attribution rather than that editorial title.
FAQ titles are labelled “Question”, Band member titles “Name”, and Song titles
“Song title”. No star rating was added because the design does not require one.

## Taxonomies and Media

- Event types (`event_type`): groups Review content.
- FAQ categories (`faq_category`): groups FAQ content.
- Repertoire categories (`repertoire_category`): groups Song content.
- Vocabularies are initially empty; category references are optional so editors
  can create content before the final grouping is agreed. Autocomplete widgets
  support new terms subject to the editor's permissions.
- Portraits and content images use Media Library and accept only Image Media.
  The optional Basic page hero video accepts only Video and Remote video Media.
  Existing Article image displays retain their dedicated wide/medium view modes.
- Images are optional to allow draft authoring while assets are collected.
- Price wording is a display string, not a numeric price or commerce field.

## Displays and future implementation

Standard form widgets and basic default displays are configured. Display order,
homepage-featured controls, and hero Media are hidden from generic entity output;
templates and Views will explicitly use them. Lower display-order numbers should
sort first when building the Views, with a stable secondary sort.

These fields do not create pages, listings, navigation links, a homepage carousel,
video autoplay, or a Webform. Those are subsequent build tasks. The contact form
will reuse Stiff Promotions' form without Band Preference as recorded in
[BUILD-NOTES.md](BUILD-NOTES.md). No roles or permissions were changed.

## Deployment and maintenance

The full site configuration was exported to `sync/`, including existing site
configuration as well as these additions. Use the normal configuration import
workflow on a matching installation; Composer supplies dependencies separately.
This export includes the earlier Article Image-to-Media conversion. A destination
that already has populated original Image storage needs a migration before import.

`scripts/build-content-model.php` records the construction through Drupal APIs.
It skips existing field instances and stops on incompatible field storage. The
script is a build utility, not an update hook or a substitute for configuration
deployment. Do not rerun core content-type recipes over the customised model.
