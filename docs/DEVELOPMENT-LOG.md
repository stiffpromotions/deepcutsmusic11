# Development log

## 2026-11-16 — Maintenance page and Editor account

- Added the themed maintenance template, `_maintenance.scss` and maintenance-only
  browser title/noindex metadata. A gold Admin login link leads to Drupal's login
  with a homepage destination. The editable message reads “We’re fine-tuning the
  Deep Cuts website. Please check back soon.” Site maintenance remains disabled.
- Created the non-administrator `editor` role and active **Iain** account using
  the supplied email. A random initial password was generated without disclosure;
  no welcome/reset email was sent. Password setting uses the standard reset flow
  or administrator account edit. Existing accounts/roles were left unchanged.
- Granted content CRUD across existing node/Media types, taxonomy-term editing,
  Paragraphs authoring, editorial SEO, revision view/revert, content tools and
  maintenance-mode preview. Configuration, user/role management, structural
  changes and Webform definition administration remain denied. Exported the role
  and maintenance message and documented account/database deployment.
- Verified content CRUD, Media creation, page editor controls and configuration
  route denials as Iain. In-memory maintenance testing confirmed the actual 503
  renderer, noindex, title/login button and login/reset exemptions without taking
  the site offline. Site/CKEditor Sass compile; Safari desktop/375px review passed.
  Temporary preview removed. See `docs/MAINTENANCE.md` for operation and setup.

## 2026-11-16 — Circular mobile toggle refinement

- Matched the visible mobile menu button diameter to the branding image's shared
  height token (36px), changed its frame to a circle, and scaled the staggered
  strokes proportionally. Preserved the cream/purple open state and a 44px touch
  area using a transparent pseudo-element around the visible button.
- Recompiled site and CKEditor CSS; whitespace checks pass.

## 2026-11-16 — Branded mobile navigation

- Reviewed Stiff Promotions' mobile menu in Safari at 375px. Right-aligned the
  Deep Cuts navigation and booking button, keeping Bootstrap Collapse and its
  existing 768px desktop breakpoint.
- Added `scss/_menu_mobile.scss` to the theme compilation. The 48px toggle uses
  a fine cream frame and slim staggered strokes, changing into a purple cross
  on cream when expanded. Added visible keyboard focus, a gold current-page
  indicator and reduced-motion-aware icon transitions.
- Open menus use opaque brand purple for contrast over photos; the header also
  stays opaque through Bootstrap's collapse transition. Long menus can scroll
  within the viewport, accounting for the Drupal administration toolbar.
- Replaced only the icon's decorative markup in `page.html.twig`; Bootstrap
  continues to control the panel, `aria-expanded` and native button behaviour.
  Removed the superseded hero-specific default Bootstrap icon styling.
- Site and CKEditor Sass compile; whitespace checks pass. Safari 375px review
  confirmed closed/open styles on standard and hero headers, right alignment,
  current Videos indication, and navigation from Home to Videos.

## 2026-11-16 — Polished public user pages

- Added all public account styling in the theme's `scss/_user.scss`, imported by
  `style.scss`: centred white panel, lavender backdrop, purple accent, restrained
  shadow, compact headings, visible account tabs and full-width action buttons.
- Improved field/label spacing, focus/error styles and wrapping on small screens.
  Styling is scoped to `body.path-user`; Drupal forms, autocomplete, validation
  and account behaviour are unchanged. Gin administration styling is unaffected.
- Compiled site and CKEditor CSS, rebuilt caches and visually checked anonymous
  login on desktop and password reset at 375px in Safari. Account links navigate
  correctly; no credentials entered or password-reset messages sent.

## 2026-11-16 — SEO foundations and rollback snapshot

- Before changing code/database, archived the complete checkout (including
  uncommitted files, settings, uploads and `.git`) and exported the DDEV database
  to the sibling `deepcutsmusic11-backups/pre-seo-20261116T075455Z` folder.
  Verified archive members, SQL contents and SHA-256 checksums; restricted access
  to the owner and saved restoration instructions. No restore was performed.
- Installed Metatag 2.2.7, Token 1.18.0, Redirect 1.13.0, Pathauto 1.16.0,
  Simple XML Sitemap 4.2.3, Schema.org Metatag 3.1.0 and Real-time SEO 2.2.0.
  No existing dependency versions were upgraded during the installation.
- Added editable metadata/sharing-image/optional SEO-analysis fields to Basic
  pages and Articles. Seeded distinct metadata without rewriting visible copy;
  existing aliases remain manual. Future Articles use `news/[node:title]`.
- Added a branded sharing JPEG and focal-point social image style. Open Graph
  and Twitter inherit Metatag title/description unless explicitly overridden.
  Real-time SEO analyses full Paragraphs output with metadata editing disabled.
- Added `deepcuts_seo` for public HTTPS canonical URLs, environment noindex,
  content exclusions and sitemap safeguards. MusicGroup/WebSite/page JSON-LD
  uses stable IDs and confirmed social details. No placeholder-video, event or
  rating assertions were added. The sitemap includes the eight main pages and
  excludes policies, utility pages and supporting entities. Robots.txt's sitemap
  declaration is managed through Composer scaffold append configuration.
- Public indexing remains off pending launch; the production switch and
  Search Console/social-preview checks are documented in `docs/SEO.md`.
  Configuration exported to `sync/`; content-level metadata requires the normal
  database/content deployment workflow. No public deployment was performed.
- Verification passed: anonymous metadata/social tags and parsed JSON-LD on ten
  public pages; utility/404 exclusions; eight canonical sitemap URLs; fetchable
  1200×630 fallback; public/staging policy and HTTPS/host redirects; editor form
  controls and Paragraphs analysis; image overrides; noindex sitemap safeguards;
  Article aliases and automatic 301s after alias changes. Fixtures were rolled
  back and caches rebuilt. Supporting-content restrictions and Videos regression
  checks pass; Composer reports no security advisories and whitespace checks pass.

## 2026-11-16 — Sound Choice SEO reference saved

- Read the user-supplied `sound-choice-drupal-seo-knowledge-export.md` and saved
  an unchanged copy under `docs/references/`, linked from `PROJECT.md`.
- Retained its evidence distinctions and source-specific caveats as background
  for a future Deep Cuts implementation discussion. Embedded instructions remain
  reference content; no modules, metadata, schema, sitemaps or site behaviour
  were changed.

## 2026-11-16 — Videos matched to Figma

- Accessed the full `/videos` reference through the Figma desktop app and read
  its preview content. Restored the supplied Recorded LIVE introduction and
  Credits including all five production/photography credits.
- Replaced tinted video panels with white, left-aligned headings and full
  1200px content-width players. Matched the bordered intro, desktop 80px rhythm
  and full-width pale-lavender Credits with responsive spacing.
- Kept both replacement YouTube URLs and Media items. Text, Credits and ordered
  Video sections remain editable through existing Drupal Paragraphs controls.
  The existing shared hero placeholder remains pending final header assets.
- Added `scripts/update-videos-figma.php` and `scripts/data/videos.json`; the
  initial builder now includes the correction, while repeat runs preserve
  existing copy and Media. Updated maintenance docs and verification checks.
- Site and CKEditor Sass compile. Safari desktop and 375px responsive layout
  inspected: intro, white players and Credits fit/wrap. Both replacement players
  display thumbnails/controls; full playback not exercised.

## 2026-11-10 — Mobile Packages quote button

- Moved the introduction's quote button after both text columns in the content
  markup. Mobile now reads all copy before the CTA; from 768px the CSS grid
  keeps the CTA beneath the left column and the details in the right column.
- Updated the initial source HTML and applied a targeted revisioned content
  update with `scripts/update-packages-cta.php`, preserving authored copy.
- Recompiled CSS, rebuilt caches and verified the rendered button follows both
  columns; existing Packages content/image checks passed.

## 2026-11-10 — Packages page

- Built `/packages` from Figma using the existing Package and Add-on types;
  no new content type was required. Added Electric, Acoustic and Roaming styles
  plus eleven Add-on nodes, retaining full copy, bold emphasis, ordering and
  enquiry/repertoire links. No prices were invented.
- Page sections: bordered two-column introduction, Styles page breaker, Package
  listing, Upgrades & Add-ons page breaker and Add-on listing. Introduction
  columns are explicit Full HTML blocks within the existing Text paragraph;
  they stack below 768px rather than relying on newspaper-style text columns.
- Imported three Image Media items from `_resources/Images/Packages`. Added
  `dc_package_style` Media mode and 307×260 focal-point crop image style so
  larger replacements can use the same layout. Original files are retained.
- Added unlinked Package/Add-on card titles and untrimmed bodies. Card grids
  use three columns from 992px, two from 768px, and one below 768px. When the
  add-on count is 3n+2, the final two cards share the last desktop row. Hover
  and focus-within use lavender background/gold border; motion preferences are
  respected. Styles are in `_paragraphs.scss`. Reduced the generic listing
  selector specificity so component grid overrides apply correctly.
- Extended canonical-page restrictions to Package and Add-on nodes. Their
  standalone URLs show the branded 404; public listings and editing remain
  available. Each new Package references the current Add-ons as available
  options. Existing authored content is preserved by repeat setup.
- Build: `ddev drush php:script scripts/build-packages-page.php`; source copy in
  `scripts/data/packages.json` and `packages-intro.html`. Supporting image and
  display configuration exported to `sync/`. See PACKAGES.md for editing.
- Passed `scripts/verify-packages-page.php` (complete ordered copy, card counts,
  image dimensions/alt text, HTTP 200 and unlinked titles) and extended canonical
  route checks across all six supporting types. CSS compiled and caches rebuilt.
  Visually verified desktop three-column layout, add-on hover and 375px stacked
  mobile cards. The shared hero placeholder remains pending final header assets.

## 2026-11-10 — About page and Band member cards

- Built `/about` from Figma: bordered “What we do” image/text introduction and
  Packages button, pale-purple “Who we are” page breaker, and a View listing
  Callum, Jack, Lee and Phil. Reused the existing Band member content type;
  no new content type was required. Source copy is in `scripts/data/about.json`.
- Imported the five supplied `_resources/Images/About` JPEGs as reusable Image
  Media, with descriptive alternative text. Originals stay intact; Drupal
  generates derivatives using `dc_about_intro` (480×450) and
  `dc_member_portrait` (420×260) focal-point scale-and-crop styles.
- Added corresponding Media view modes. Image-and-text paragraphs now have an
  optional bordered introduction and Image preset choice (Natural or About
  introduction). Other image-and-text sections retain their natural setting.
  Band member cards use the portrait mode and untrimmed biographies, names
  without canonical links, italic role text and two columns from 768px. Styles
  remain in `_paragraphs.scss`.
- Extended the custom canonical-page restriction to `band_member`; direct
  requests show the branded error page with HTTP 404. About's View and normal
  editing routes remain available. Future supporting content types should be
  added to the restriction as they are introduced, per the user's instruction.
- Repeatable setup: `ddev drush php:script scripts/build-about-page.php` after
  content-model/page/paragraph setup. Existing Media, Band member nodes and page
  sections are preserved. Supporting configuration is exported to `sync/`.
- Passed `scripts/verify-about-page.php`: HTTP 200, four ordered complete
  biographies, one H1, no member-title links, five actual image derivatives
  returning the expected dimensions and alt text. Extended and passed the
  canonical/edit-route checks for Band members. Compiled CSS, rebuilt caches,
  and visually checked desktop and 375px mobile layouts in Safari.
- The header still uses the approved homepage placeholder pending final hero
  assets. See ABOUT.md for the image replacement and editing workflow.

## 2026-11-10 — Thank-you page

- Built the existing `/thank-you` Basic page using Figma's “Thank you” hero and
  bordered “Music to our ears” Text paragraph. Preserved the two-working-day
  response copy and added links to Book now, FAQs and the confirmed Instagram
  profile. Reused existing responsive components and hero placeholder.
- Added `scripts/build-thank-you-page.php`, preserving existing sections on
  repeat runs. Verified HTTP 200 and rendered confirmation text. No new theme
  styles or configuration needed. The enquiry form confirmation destination
  remains to be connected when that form is built.

## 2026-11-10 — FAQ canonical 404

- Added FAQ to the canonical-page restriction alongside Song and Review. Direct
  FAQ URLs display the configured `/error` page with HTTP 404 at the requested
  URL. The FAQ View and normal editing permissions remain available.
- Extended the verification script to check FAQ canonical/edit routes, a real
  anonymous FAQ URL, and the `/faqs` listing. Rebuilt caches and passed checks
  for all three restricted content types and their public listing pages.

## 2026-11-10 — FAQ, Privacy Policy and Cookie Policy

- Read the Figma Sites previews for all three pages, including the complete FAQ
  answers and policy copy. Added 16 FAQ nodes across four `faq_category` terms:
  Pricing & Booking (3), Planning & Details (4), Travel & Location (2), Logistics
  & Tech (7). Existing nodes are preserved when the build script is repeated.
- Added category arguments to `deepcuts_faq` and allowed FAQ categories in the
  Content listing paragraph category field. The FAQ page has four filtered
  listings and the “Have another question?” contact CTA. Answers remain visible
  in bordered cards as shown in Figma; category headings use pale purple bands.
  FAQ paragraph styles remain in `_paragraphs.scss`.
- Imported Privacy and Cookie policies into Text paragraphs, preserving source
  dates (08 February 2026 and 01 November 2026). Restored readable lists and two
  semantic cookie tables from the flattened Figma text. The cookie-purpose
  source ends in an incomplete “Google Fon” fragment; no missing row wording
  was invented. The complete Google Fonts row in Cookie details was retained.
- Added `_policies.scss` for headings, spacing, table borders and horizontally
  scrollable, keyboard-focusable table containers. Policy paragraphs use the
  existing Full HTML format, whose editor already supports tables. Linked
  enquiry, Packages, policy, email and external reference destinations.
- Reused the approved hero placeholder on all three pages. Footer navigation
  remains the entry point. No standalone FAQ-page restriction was added.
- Repeatable build: `ddev drush php:script scripts/build-information-pages.php`
  after the existing content/page/paragraph setup. Source copy lives in
  `scripts/data/faqs.json`, `privacy-policy.html`, and `cookie-policy.html`.
  Only the two supporting configuration records are exported; content remains
  in the database and can be recreated using the script.
- Verification: CSS compiled and caches rebuilt. Passed
  `scripts/verify-information-pages.php`: category membership/counts, published
  results, HTTP 200 on all pages, single H1, 16 visible FAQ cards and two tables
  with captions/column headers. Visually checked desktop FAQs and mobile Cookie
  Policy at 375px. Policy wording still needs reconciliation with the actual
  integrations and consent behaviour before launch; see BUILD-NOTES.md.

## 2026-11-10 — Review canonical 404

- Extended `deepcuts_content` to return the branded 404 page for standalone
  Review canonical URLs as well as Songs. Listings and edit routes retain normal
  entity access. No configuration change is required.
- Extended `scripts/verify-song-pages.php` to cover Review canonical/edit routes
  and real HTTP requests to a Review URL and `/testimonials`.

## 2026-11-10 — Song canonical 404 and Figma error page

- Added and enabled custom module `deepcuts_content`. Its request subscriber
  throws `NotFoundHttpException` for `entity.node.canonical` when the converted
  node bundle is Song. Drupal renders its configured error page at the original
  URL with HTTP 404. This also covers aliases resolving to the canonical route.
  Node view access is unchanged, keeping Songs available to Views and editing.
- Built `/error` (existing node 12) with Figma's “Mic drop…” hero title and
  bordered “... but not how we intended” message, with working homepage/contact
  links. The microphone photo is absent from `/_resources`; retained the approved
  homepage-photo placeholder until the final image is supplied. Reused existing
  responsive hero and Paragraph styling, header and footer.
- `scripts/build-error-page.php` creates the page if absent, preserves existing
  sections on repeat runs, configures `system.site:page.404`, and exports the
  site/module configuration. Run after the content-model/page/paragraph scripts,
  with `ddev drush en deepcuts_content -y` enabling the module on a fresh build.
- `scripts/verify-song-pages.php` passed: real anonymous Song and missing-page
  requests return 404 with the branded message; `/error` returns 200 directly;
  `/repertoire` returns 200 and still includes song text. Subscriber checks
  confirm the editing route is unaffected. Visually verified `/node/20` in
  logged-in Safari displays the branded error page. Caches rebuilt.

## 2026-11-10 — Testimonials page and Figma reviews

- Imported eight individual Review nodes from Figma's `/testimonials` design,
  in visual reading order. Preserved the six Festival Nights credits in a new
  plain-text `field_review_note`, editable separately from the quote/attribution.
  Quotes and attributions are recorded in `scripts/data/testimonials.json`.
- Added a Reviews option to Content listing paragraphs. The existing
  `deepcuts_review` View now has an `all_reviews` embed display showing published
  reviews, sorted by Display order then node ID. Its default display retains the
  featured-only filter for homepage reuse. No imported review is automatically
  marked as featured.
- Added the listing and Figma's “Want to read more?” page breaker to the existing
  Testimonials Basic page. “Get in touch!” links to Book now. Used the approved
  homepage-photo placeholder for the hero until the final header assets arrive.
- Themed cards in `_paragraphs.scss`: pale purple background, fine border,
  centred semibold quotations, smaller attribution and italic provenance note.
  Natural-height cards form two columns from 768px and a single column below.
- Repeatable setup: `ddev drush php:script scripts/build-testimonials.php` after
  the existing content-model, page and paragraph scripts. Existing reviews and
  page sections are preserved. The script exports only its supporting config;
  nodes and paragraph content are recreated by the script on a fresh build.
- Verified desktop and 375px mobile rendering in Safari. Extended and passed
  `scripts/verify-paragraphs.php` to check the all-reviews display includes both
  featured and ordinary published reviews, excludes unpublished reviews, and
  renders through a Content listing paragraph. Fixtures rolled back; caches
  cleared. Re-ran the setup successfully without replacing authored content.

## 2026-11-10 — Figma footer

- Built the shared purple footer with cream logo, Contact, FAQs, Privacy Policy,
  Cookie Policy, Instagram and the existing gold Nav button for Book now.
- Footer menu links remain editable at `/admin/structure/menu/manage/footer`.
  Contact and Book now currently both point to `/book-now`, the planned enquiry
  form page. Instagram uses the confirmed `https://www.instagram.com/deepcutsmusic/`.
- Added `menu--footer.html.twig`, footer menu preprocessing, and `_footer.scss`,
  imported in `style.scss`. The logo links home; Instagram has an accessible
  name and a decorative inline SVG. Links have visible keyboard focus.
- Added repeatable `scripts/setup-footer.php` because menu links are content and
  are not included in configuration exports. Run using
  `ddev drush php:script scripts/setup-footer.php` after initial page setup.
  It updates matching footer links without changing the primary menu.
- Recompiled CSS, rebuilt caches, and visually checked Safari at 1440px and
  375px. Desktop displays one row; mobile centres the logo and wraps links and
  actions. Checked rendered link destinations. No mobile-header changes made.

## 2026-11-10 — Consistent expanded-navigation logo

- Overrode Bootstrap5's logo breakpoint in `_header.scss`: the 60px desktop
  height now applies from 768px, matching the expanded navigation. Below 768px
  the existing 36px height remains. No separate header-padding change was found;
  the logo height was changing the navbar's overall height at 992px.
- Retained the user's removal of compact navigation spacing and disabled FAQ
  main-menu item. Recompiled theme CSS and rebuilt Drupal caches.

## 2026-11-10 — Revised navigation breakpoint

- Changed the navbar to `navbar-expand-md`: the hamburger appears below 768px.
- Moved compact navigation typography and spacing to 768–991.98px; standard
  font sizes and spacing resume at 992px. This supersedes the breakpoint below.
- Recompiled site/editor CSS and rebuilt Drupal caches. Checked Safari at 768px:
  all links and Book now fit on one row. At 767px the hamburger appears.

## 2026-11-10 — Navigation CTA and compact desktop spacing

- Added a custom main-menu template and menu preprocessing that identifies the
  node linked by `/book-now`, rather than relying on its label or menu position.
  Applied the existing Figma `btn btn-nav` variant to this CTA. It omits the normal
  `nav-link` class so hero link colours do not override button hover text.
- Imported `_menu_desktop.scss`: desktop items remain on one row from 992px;
  992–1199.98px uses 14px text, tighter link gaps/padding, and a compact CTA.
  Larger widths retain the standard typography and button dimensions.
- Confirmed the page template already uses `navbar-expand-lg` (992px), retaining
  that breakpoint. Hamburger remains visible below 992px. The CTA also receives
  spacing within the expanded mobile menu.
- Recompiled site/editor CSS, rebuilt caches, and checked rendered classes.
  Safari responsive preview at exactly 992px shows all items and the gold CTA on
  one row without the hamburger; at 991px it shows the collapsed hamburger.
- No menu content, footer, or Drupal configuration changes were made.

## 2026-11-10 — Centre Repertoire song headings on mobile

- Below 768px, centred song-list section headings on Repertoire (node 5) to
  match the song text. Desktop headings and other nodes remain unchanged.
- Added a node-ID class to the Basic page template for precise scope. The rule
  lives in `_paragraphs.scss`; compiled site/editor CSS and rebuilt Drupal caches.

## 2026-11-10 — Paragraphs and Repertoire page body

- Installed Paragraphs 1.23.0 and Entity Reference Revisions 1.15.0 via Composer.
  Created the six agreed types: Text, Image and text, Card group, Reviews, Page
  breaker, and Content listing. Added Page sections to Basic page and configured
  widgets, defaults, constrained options, and revision-aware rendering.
- Built seven controlled listing Views and a shared content card view mode.
  Added section/card templates, theme preprocessing, and optional swipeable review
  carousel behaviour. All component styling is in `_paragraphs.scss`.
- Read the Figma Repertoire preview and imported 171 Song nodes in four category
  terms, preserving supplied wording/order. Added a bordered intro, four lavender
  category listings, and the closing additional-song Page breaker.
- Kept the existing hero placeholder, header, and footer. The original Basic page
  Body is retained but not rendered when Page sections are present.
- Verified forms, editor-context validation, listing filters/counts, anonymous
  output, PHP syntax, CSS compilation, and Safari desktop/370px visual layout.
  Tested all section render paths and unpublished-review exclusion with rolled-back
  fixtures. Comprehensive carousel interaction review awaits real review content.
- Exported active configuration to `sync/`; export also includes pre-existing
  active account-menu and Basic page teaser display changes. No header/footer
  configuration was intentionally changed by this task.
- Added `docs/PARAGRAPHS.md` with authoring, file paths, data, checks, and deployment
  instructions. Nodes/paragraphs/terms require content deployment separately from
  Composer and config import.

## 2026-11-09 — Replace Repertoire hero placeholder

- User confirmed final headers have not been supplied and requested the homepage
  intro image as the placeholder. Located it at
  `_resources/Images/Homepage/Homepage intro header.jpg` (8192 × 5464).
- Replaced Media 1's image reference, preserving the page's hero assignment;
  renamed the Media item to identify the homepage placeholder. Old image/file
  records were not deleted. Set focal point to 45%/35%.
- Successfully generated wide 1440 × 400 and mobile 768 × 512 derivatives and
  rebuilt caches. Updated `docs/HERO.md`. No configuration changes required.

## 2026-11-09 — Repertoire responsive hero

- Added the reusable full-width image hero with decorative responsive picture,
  black overlay, real node-title H1, and suppression of the duplicate title block.
- Added 18:5 desktop and 3:2 mobile crops at the 768px breakpoint, six focal-point
  image styles, responsive image mapping, and Image Media focal-point widgets.
- Installed Focal Point/Crop via Composer. Added transparent-to-purple navigation
  scroll behaviour for hero pages with reduced-motion support and mobile menu
  contrast. Preserved existing region rendering and non-hero layout behaviour.
- The supplied Dropbox archive contains no Site Headers files. Imported the
  matching acoustic thumbnail as explicitly labelled temporary Media 1 and attached
  it to Repertoire pending the full-resolution header asset. Set focal point to
  50%/28% to favour faces in the wide crop. No substitute artwork was invented.
- Compiled CSS, rebuilt caches, exported active configuration, checked one H1 and
  picture/JS output, generated desktop/mobile derivatives, and inspected Safari
  desktop/370px layouts. Final image and comprehensive scroll/menu testing remain.
- Export also reflects pre-existing active changes to theme settings/branding and
  removal of breadcrumb/powered-by blocks; these were not changed by the hero setup.
- See `docs/HERO.md` for editing, implementation paths, and deployment notes.

## 2026-11-09 — Create initial site pages and navigation

- Created 12 published Basic page nodes and aliases corresponding to the Figma
  page structure. Used short placeholder text, without inventing testimonials,
  package details, policy wording, or band biographies.
- Set Home as the front page, assigned Mic drop… as the custom 404, and added
  main menu and footer policy links. Kept the existing core Home menu link.
- Added `scripts/build-site-pages.php` to record repeatable initial creation;
  existing matching nodes/content are preserved. Documented node/URL mappings in
  `docs/CONTENT-MODEL.md` and the distinction between content and config deployment.
- Exported the changed `system.site` settings to `sync/` and rebuilt caches.
- Verified anonymous HTTP 200 for the root and all 11 other page aliases, plus
  HTTP 404 with the custom Mic drop content for a missing path. Verification
  used the configured Host header because localhost is not a trusted hostname.
- No individual Reviews, FAQs, Packages, Songs, Media, Views, or Webform were
  created. These landing pages await their component and content builds.

## 2026-11-09 — Create the structured content model

- Preserved Article and Basic page. Created Review, FAQ, Band member, Package,
  Add-on, and Song, with fields documented in `docs/CONTENT-MODEL.md`.
- Added Event types, FAQ categories, and Repertoire categories vocabularies without
  inventing taxonomy terms. Added optional Basic page hero image/video/subtitle
  fields, package/add-on references and price wording, and sorting/featured controls.
- Image Media references accept only images. Basic page hero video is the only new
  field that accepts local or remote video Media. Existing Article image handling
  is preserved. Created default form and view displays; editorial controls and
  hero media are hidden from generic output pending templates/Views.
- Applied `scripts/build-content-model.php` through Drush. Verified all eight node
  editing forms build and reviewed field types/required flags through Drupal APIs.
- Exported the full active site configuration with `ddev drush config:export -y`
  to `sync/`, including earlier site changes. Cache rebuild succeeded. No sample
  content, Views, taxonomy terms, or role/permission changes were made.
- Configuration deployment to a populated original Article Image field requires
  migration; ordinary import cannot safely replace populated field storage.

## 2026-11-09 — Record supplied design/build correspondence

- Added `docs/BUILD-NOTES.md` and linked it from the project overview.
- Recorded Stiff Promotions contact form reuse without Band Preference, cream
  logo and Dropbox assets, muted autoplay hero with unmute control, transparent
  header transitioning to purple/white on scroll, policy table repair, responsive
  layout responsibility, and Options & Add-ons hover behaviour.
- Distinguished reported YouTube manager access from verified access and upload
  availability. Recorded the graphical Packages redesign as deferred, and SEO/AI
  experimentation as a proposal rather than approved scope.
- Documentation-only change; no external accounts accessed or site code changed.

## 2026-11-09 — Article Image field uses image-only Media

- Confirmed no Article nodes exist, the original image storage contains no data,
  and Article is its only bundle. Existing Image Media and Media Library are
  already installed.
- Replaced the empty `node.article.field_image` image field with a single-value
  entity reference targeting Media, retaining the machine name and Image label.
  Restricted the entity selection handler to the `image` Media bundle and set
  the form widget to Media Library. Other Media types remain available elsewhere.
- Added dedicated Image Media view modes `article_image` (wide/eager) and
  `article_teaser` (medium/lazy) and changed Article displays to render Media.
  These preserve image sizing without changing the site's default Media display.
  The old teaser formatter's link to its parent node is not carried over: these
  Media images render without a link.
- Applied the guarded script `scripts/article-image-media.php`. It refuses to
  replace an original Image field with content or shared storage, so populated
  environments require a migration. No content or files were created or removed.
- Verified field type/target, image-only bundle restriction, widget and displays,
  and successfully built the Article form through Drupal's API. PHP lint and
  cache rebuild passed. Browser upload/selection was not tested.
- Changes are in active configuration; final configuration export is still
  required for the normal configuration deployment workflow. Do not reapply the
  original core Article recipe over this changed field storage.

## 2026-11-09 — Figma palette and component foundations

- Inspected the Figma Styles swatches and exported CSS for Navigation Button,
  Primary Button, and Secondary Button Default/Hover/Click variants.
- Centralised palette and Bootstrap colour/state overrides in the custom theme's
  `_variables_bootstrap.scss`. Preserved the distinction between Styles primary
  purple `#572998` and button purple `#512B92`, and between Styles Purple/50
  `#F5F1FB` and button text `#F6F3FC`.
- Added/imported `_buttons.scss`, `_pager.scss`, `_surfaces.scss`, and the existing
  `_links.scss`. Navigation/primary buttons use gold, secondary uses purple;
  component hover/pressed colours, 16px/24px padding, 10px corners, and 600-weight
  button text follow Figma. Bootstrap brand primary remains purple, while the
  `.btn-primary` CTA is explicitly gold to match the named Figma component.
- Pager, focus, disabled, and content-link behaviour extend the palette; these
  are implementation choices rather than separately verified Figma variants.
  Figma's light text on gold was preserved; contrast adjustments may be needed
  following design review.
- Added `docs/theme-components.html`, a static component review page with no
  Drupal content or configuration changes. Recompiled site and CKEditor CSS,
  rebuilt caches, verified HTTP 200, and visually inspected the rendered reference
  in Safari. Hover/pressed values were checked in generated CSS, not exhaustively
  browser-tested. New navigation/surface classes await use in page templates.

## 2026-11-09 — Self-host Figma typography

- Installed Google Fonts Rethink Sans normal weights 400/500/600/700 and OFL.txt
  in the custom theme's `fonts/rethink-sans/` directory.
- Added `_fonts.scss` and `_typography.scss`, Bootstrap typography variable
  overrides, and compiled site/editor styles. Added the font stylesheet to the
  theme library and CKEditor 5 stylesheet list.
- Matched the Figma Styles frame's sizes, weights, leading, and letter spacing;
  retained Bootstrap RFS for responsive headings. Added explicit hero and
  navigation classes for later template integration. Annotation-only Roboto Flex
  is not installed.
- Fixed the pre-existing missing `root.scss` import in `ck5style.scss` by importing
  Bootstrap's root dependencies with custom variable overrides. Set the editor
  content container's family, font size, and leading explicitly.
- Compiled all three CSS entry points successfully. Upstream Bootstrap Sass emits
  deprecation warnings. Checked font file signatures and compiled typography,
  rebuilt Drupal caches, and checked font/CSS HTTP responses. Browser visual
  comparison and editor interaction remain to be done during page implementation.
- Theme README records build commands, source, licence, and usage.

## 2026-11-09 — Adjust Git ignores for Composer deployment

- Updated the ignore-file description to reflect Drupal 11's root-level layout.
- Ignored `.DS_Store`, `node_modules/` at any level, contributed profiles, and
  the entire root `private-files/` directory.
- Preserved the explicit exception for `sites/default/settings.local.php`:
  the user confirmed this is shared configuration for both local and live sites.
- Kept Composer manifests, exported configuration in `sync/`, custom code,
  recipes, documentation, and compiled custom theme CSS eligible for Git.
  DDEV remains ignored. No deployment-script changes were made.
- Verified representative paths with `git check-ignore --no-index`, including
  both ignored runtime/dependency paths and retained project files.

## 2026-11-09 — Add Article and Basic page using recipes

- User confirmed Article and Basic page as the requested content types. Neither
  existed before this work.
- Applied `ddev drush recipe core/recipes/article_content_type -y` successfully.
  Article includes Body and Image; tags and comments are separate recipes and
  were not applied.
- Applying `core/recipes/page_content_type` stopped at its strict pre-existing
  configuration check: `core.entity_view_mode.node.full` has language `en-gb`
  on this site, versus `en` in shipped configuration.
- Added `recipes/deepcuts_page_content_type`, copying the core Basic page
  configuration unchanged and using `config.strict: false` to preserve existing
  configuration while adding missing configuration. Applied it successfully
  with `ddev drush recipe recipes/deepcuts_page_content_type -y`.
- Rebuilt caches and verified both node types, Body fields, Article Image field,
  and default form/view displays through Drupal's API. Confirmed the existing
  shared Full content view mode retains `en-gb`. No sample content was created.
- These changes are in active database configuration. No configuration export
  was performed. The custom recipe's README documents reproduction and scope.

## 2026-11-09 — Repair Gin administration settings display

- Inspected Gin 5.0.16 and Gin Toolbar 3.0.3 and the active configuration.
  Gin was the admin theme, but `gin_toolbar` was downloaded rather than enabled.
  Core Toolbar and Admin Toolbar were enabled; core Navigation was disabled.
- Enabled the existing Gin Toolbar module with `ddev drush en gin_toolbar -y`.
  Gin declares this package as a Composer dependency, so no dependency update
  was needed.
- Found blank `preset_accent_color` and `layout_density` values in `gin.settings`.
  Restored these to Gin's shipped `blue` and `default` values through Drupal's
  configuration API. Preserved horizontal navigation, automatic dark mode,
  and the remaining preferences. The origin of the blank values is unknown.
- Rebuilt caches with `ddev drush cr`. Reloaded the authenticated settings page
  in Safari and verified selected defaults and a clearly visible blue
  **Save configuration** button at the bottom of the form. No core or contributed
  source files were modified. Form submission was not tested.
- Changes are in the local active database configuration; no configuration
  export was performed.

## 2026-11-09 — Install FitVids.js through Composer

- Added `jquery/fitvids` 1.2.0 to the root Composer requirements and lock file.
  Used the package definition supplied by the installed Drupal FitVids module
  (`modules/contrib/fitvids/composer.libraries.json`) directly in the root
  repositories list, avoiding an additional Composer merge plugin.
- The existing `drupal-library` installer mapping installs the plugin at
  `libraries/fitvids/jquery.fitvids.js`, the path expected by the Drupal module.
- Reproduction: run `ddev composer install` from the project root.
- Verification: Composer installed one package with no other package updates
  or removals and reported no security advisories. Composer validation passed
  with an advisory about the deliberate exact version constraint. Drupal cache
  rebuild succeeded and the installed JavaScript file was checked.
- This installs the library only; module enablement and video behaviour were
  not changed or browser-tested.

## 2026-11-09 — Translation module uninstall error

- Investigated a missing `locales_source` table error when Drupal translated
  the module uninstall confirmation message.
- Verified through DDEV/Drush that Drupal bootstraps and connects to the database.
  `locale`, `content_translation`, and `config_translation` are not enabled;
  `language` remains enabled. The default language is `en-gb`.
- Confirmed `locales_source` and `locales_target` are absent, consistent with
  Interface Translation (`locale`) having been uninstalled.
- The supplied stack trace and core uninstall form indicate that the error
  occurred after module uninstall, while the same request tried to translate
  its success message using the prior translation service.
- Ran `ddev drush cache:rebuild` successfully. Verified the local homepage
  returns HTTP 200 using `ddev exec curl` against `http://localhost`.
- No tables were recreated and no modules were installed or uninstalled during
  recovery. Reload the browser page to start a fresh request.

## 2026-11-09 — Project baseline

- Recorded the project brief: Deep Cuts band website, managed by Stiff
  Promotions, built with Drupal 11, PHP, and Bootstrap using forthcoming Figma
  designs and user direction.
- Inspected Composer manifests, the existing README, DDEV configuration, Git
  status, and custom-code directory presence.
- Created [PROJECT.md](PROJECT.md) as the project overview and this file as the
  ongoing implementation and decision log.
- Verification: documentation-only change; observations checked against local
  files. No runtime tests performed and no application configuration changed.
- Pending: design reference, first implementation priority, and runtime audit.

## 10 November 2026 — Book Now

- Built `/book-now` with the exact Figma “Ready to book?” introduction and the
  shared bordered Text paragraph. Reused the existing hero image fallback.
- Added the editable `field_booking_form` Webform reference to Basic pages and
  the `booking` form. Retained supplied fields/options and required rules, removed
  Band preference, and used the current `webform_flexbox` element type (the old
  `flexbox` type does not render layout wrappers in this Webform version).
- Scoped Deep Cuts form styles provide two desktop columns, a single column below
  768px, purple focus/checkbox accents and the shared gold Submit button.
- Success redirects to `/thank-you`, without submission tokens in the URL.
  Submissions and mailing-list consent are stored in Drupal. Email notification
  recipient is pending user input; no mailing-list provider has been connected.
- Reproduction: `ddev drush php:script scripts/build-booking-page.php`, then
  `ddev drush cr`. Initial setup preserves existing sections/form configuration
  on reruns. Exported config lives in `sync/`; page/paragraph data needs the normal
  content/database deployment workflow.
- Verification: SCSS compilation; anonymous HTTP render; required-field and email
  validation; successful submission persistence with optional fields empty and
  consent saved; redirect to thank-you. Test submission deleted by
  `scripts/verify-booking-page.php`. Desktop appearance inspected in Safari;
  mobile breakpoint implemented but not visually inspected.

### Book Now notification settings

- Applied the user-approved completed-submission HTML email: To
  `iain@stiffpromotions.co.uk`, BCC `info@alimartin.com`, From
  `* Deep Cuts website * <noreply@deepcutsmusic.co.uk>`, Reply-To
  `noreply@deepcutsmusic.co.uk`, subject `* Enquiry from Deep Cuts website *`.
  Literal asterisks are retained as supplied. The body includes all form values,
  including consent choices and empty optional fields.
- `scripts/configure-booking-email.php` applies and verifies these settings;
  `sync/webform.webform.booking.yml` includes the enabled handler. No test email
  was sent; external delivery is not verified by configuration checks.
- The submission verification script now refuses to submit while handlers are
  enabled, preventing unintended notifications during later test runs.
# 16 November 2026 — Videos page

- Built `/videos` with Electric and Acoustic placeholder YouTube videos in the
  supplied order. Added reusable Video Paragraphs with editable headings, Media
  Library selection, background and width controls. Further videos can be added
  and reordered directly on the page without code changes.
- Used existing hero/section styles, lavender panels and responsive 16:9 players.
  Drupal's existing oEmbed formatter supplies accessible, lazy-loaded players.
- Added `scripts/build-videos-page.php`, targeted configuration exports and
  `docs/VIDEOS.md` with replacement/addition and deployment instructions. The
  setup utility preserves existing page sections and Media edits.
- Verified anonymous HTTP 200, one H1, ordered Electric/Acoustic headings, both
  accessible lazy-loaded players and working Drupal oEmbed endpoints; built the
  Media editor form. PHP syntax and site/CKEditor Sass compilation pass.
- Browser preview host unavailable; visual responsive review and actual YouTube
  playback still need browser verification. The Figma URL could not be fetched,
  so this page follows the site's established section design.


## 17 November 2026 — Homepage

- Built the full Figma Home design after the user's uppercase GO: muted looping
  Media 10 hero, transparent navigation, introduction, event cards, manual review
  carousel and Packages/Testimonials/Videos cards using the supplied images/copy.
- Added editable nested feature-card sections; imported all 14 company Logos,
  installed Image Effects and exported the requested grayscale 80px Logo style.
- Added Views for published Logos and a pinned Figma review followed by four
  distinct random promoted reviews. Homepage caching allows a fresh selection;
  other review Views remain unchanged. Added logo CRUD to Editor permissions.
- Implemented five-second full logo batches, looping partial final batches,
  responsive counts, pause/manual controls and reduced-motion handling.
- Verified content, PNG imports, image effects, View order/uniqueness/randomness
  and Editor limits with scripts/verify-home-page.php. Browser checks covered
  desktop/390px layouts, muted autoplay, Mute/Unmute and manual review navigation.
- Added docs/HOME.md and repeatable scripts/build-home-page.php; existing content
  is preserved on reruns. Compiled site/CKEditor SCSS and rebuilt Drupal caches.

### Homepage carousel refinements

- Removed manual logo arrows and Pause/Play control. Batches retain the five-second
  hold and now slide over 700ms; a hidden duplicate first batch provides a smooth
  forward loop. Reduced motion keeps the original logos available to scroll.
- Stacked review slides in a shared CSS grid cell to reserve the tallest review's
  height, including attribution/notes, automatically at every viewport width.
  Added a 350ms crossfade while keeping inactive reviews inert and hidden from
  assistive technology. Reduced motion disables fading.
- Rebuilt site/CKEditor CSS and Drupal caches. JavaScript syntax passes; browser
  checks confirmed removed logo controls, advancing batches, changing reviews and
  stable panel/card positions between reviews of different lengths.

### Full logo batches at every breakpoint

- Replaced blank final-batch spacer cells with repeated logos from the start of
  the list. Desktop now shows eight logos in every batch; smaller breakpoints
  likewise retain their full visible count. Smooth transitions and five-second
  holds remain, including the forward loop. Clones are inert and excluded from
  the accessibility tree, with duplicate IDs removed.
- Checked JavaScript syntax and full-batch construction for responsive counts
  3/4/6/8, including totals smaller than one screen; rebuilt Drupal caches.

### Card link hover styling

- Removed text underlines in normal, hover and keyboard-focus states from all
  themed card links (homepage, generic cards, offerings and members), plus
  Bootstrap cards. Existing colour treatments and focus outlines remain.
- Compiled site and CKEditor styles successfully and rebuilt Drupal caches.

## 18 November 2026 — Theme favicons

- Copied the supplied `_resources/Favicon/favicon/` set to the custom theme's
  `favicons/` directory, and replaced the theme's default `favicon.ico`.
- Attached scalable SVG and 96px PNG browser icons, the 180px Apple touch icon,
  web app manifest, Deep Cuts application names and purple theme colour through
  HTML preprocess. Drupal retains the ICO fallback. These attachments also apply
  when this theme renders maintenance/error pages.
- Made manifest icon URLs relative to the theme directory and launch/scope/ID
  URLs resolve to the site root, including subdirectory installations. Kept
  supplied Android 192px/512px artwork with `purpose: any`: its logo reaches beyond
  the maskable safe area, so marking it maskable could crop the design. Added an
  Apache/cPanel MIME declaration for `.webmanifest`.
- Verified HTTP links on Home, login and 404 pages; all five linked assets and
  both manifest icons return 200, and the manifest launch URL resolves correctly.
  PHP syntax passes and Drupal caches were rebuilt. No configuration changes
  were needed; publish the custom theme files with the beta.
