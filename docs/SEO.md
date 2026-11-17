# Deep Cuts SEO

Implemented 16 November 2026 using the Sound Choice knowledge export as reference.
This implementation retains the existing visible copy and manual page aliases.

## Rollback snapshot

Before any SEO changes, a complete project archive and database export were saved
outside the document root:

`/Users/aam/Sites/GitHub/deepcutsmusic11-backups/pre-seo-20261116T075455Z/`

- `project.tar.gz`: 210,160,140 bytes, including uncommitted code, `.git`,
  dependencies, settings, uploaded images and private files.
- `database.sql.gz`: 1,192,157 bytes, the pre-change DDEV database.
- `manifest.json`: SHA-256 checksums; archive structure and SQL contents checked.
- `RESTORE.md`: exact rollback steps. The folder and its contents are restricted
  to the owner. They contain private settings/data and must not be published.

To roll back, first preserve any later work, stop DDEV, move the modified project
aside, extract `project.tar.gz` into `/Users/aam/Sites/GitHub`, start DDEV in the
restored directory, import the accompanying database and rebuild caches. Restore
code and database together. Do not extract over the modified directory: newly
introduced files could remain. A destructive restore was not performed.

## Modules and responsibility

- Metatag, Token, Open Graph and Twitter Cards: defaults and editorial overrides.
- Schema.org Metatag, Organization, WebSite, WebPage and Article: JSON-LD.
- Redirect: permanent redirects after alias changes; internal-path normalization.
- Pathauto: `news/[node:title]` for future Articles only. Existing Basic pages
  retain their manual aliases; no bulk alias regeneration was performed.
- Simple XML Sitemap: canonical public content and cron regeneration.
- Real-time SEO: optional focus keyphrase and rendered-content analysis. Title
  and description editing in its widget is disabled; Metatag remains their owner.
- `deepcuts_seo`: environment/indexing safeguards, canonical URLs, sharing-image
  fallbacks and defensive sitemap filtering.

Installed versions are recorded in `composer.lock`. Composer also installs the
Real-time SEO JavaScript library; generated dependencies are ignored in Git.

## Editing pages and sharing previews

Basic pages and Articles have **SEO and social metadata**, **Social sharing image**
and **Editorial SEO guidance** controls. Edit title/description in Metatag. Social
titles/descriptions inherit these unless explicitly overridden. Canonical and
Open Graph URLs are enforced as the page's clean URL on the configured public
origin; they exclude tracking queries and internal `/node/…` paths.

Select an Image Media item for a page-specific sharing image. The
`deepcuts_social` image style creates a 1200 × 630 crop using its focal point.
The image's alt text is reused. With no accessible selected image, the site uses
the branded 1200 × 630 JPEG at
`themes/custom/deepcutsmusic11/images/social-share.jpg`, composed from the supplied
band photograph and cream logo. It is separate from the hero image.

Initial page titles, descriptions and focus phrases are in
`scripts/data/seo-pages.json`. They are editorial starting points, not keyword
research results. Policies have descriptions but no prescribed focus keyphrase.
The Videos description avoids claiming the replacement videos are Deep Cuts.

Real-time SEO uses the **full** rendered node and custom theme, including
Paragraphs sections. Use its analysis update button after editing sections.
Scores are diagnostics: do not pad copy or force keywords to make them green.
No new editor roles or permissions were granted. Future roles require appropriate
content-editing, text-format, Media and Real-time SEO permissions.

## Canonical origin, indexing and launch

The configured public origin is `https://deepcutsmusic.co.uk`, matching existing
deployment settings. `deepcuts_seo.settings` is the shared source for the origin,
sharing fallback and confirmed social profiles. Schema entity IDs use it too.
If the origin changes, also update `simple_sitemap.settings:base_url` and
`assets/robots.append.txt`, regenerate robots.txt through Composer and regenerate
the sitemap.

Indexing is **off by default**, including on a production hostname until launch.
The shared settings file enables it only when the production environment variable
`DEEPCUTS_SEO_PUBLIC=1` is present, and never in DDEV. The policy also checks that
each request is HTTPS on the exact configured public hostname. Local and staging
requests get both `noindex, follow` metadata and an `X-Robots-Tag` header.
This is crawler guidance, not access control; protect non-public staging through
hosting authentication as well.

At launch, after final content/media and production checks:

1. Configure `DEEPCUTS_SEO_PUBLIC=1` in the production PHP environment. Alternatively
   set `$settings['deepcuts_seo_indexing_enabled'] = TRUE` in a production-only
   settings include loaded after shared settings. Do not edit the shared default
   to TRUE. DDEV/staging must remain disabled.
2. Rebuild Drupal caches after changing that setting.
3. Verify a public HTTPS page returns indexable robots metadata with no noindex
   header. Check policies, utility pages, sitemap URLs and image accessibility.
4. Verify HTTP and `www` redirect to the public HTTPS origin in a single hop.
   The module supplies this for safe requests once public indexing is enabled;
   configure hosting-level HTTPS enforcement too, including static resources.
5. Validate production URLs in Schema.org Validator and, for eligible types,
   Google's Rich Results Test. Check actual social shares and cached previews.
6. Verify Search Console ownership and submit `/sitemap.xml` using the site's
   account. Neither account verification nor submission was performed locally.

Rebuild caches when switching any environment between public/private indexing.
Never assume copying the live database makes a staging site safe automatically.

## Indexing and sitemap policy

| Content | Production indexing | Sitemap |
| --- | --- | --- |
| Eight main pages: Home, About, Packages, Videos, Repertoire, Testimonials, FAQs, Book now | Allowed after launch | Included |
| Published future Basic pages and Articles | Allowed unless marked noindex | Included unless excluded |
| Privacy and Cookie policies | Allowed after launch | Excluded |
| Thank-you and direct Error page | Noindex | Excluded |
| Actual 403/404 responses, user/admin/search/non-content routes | Noindex | Excluded |
| Songs, Reviews, individual FAQs, Band members, Packages and Add-ons | Existing standalone 404 restrictions retained | Excluded |
| Taxonomy, Media and unpublished content | No public indexing policy | Excluded |

The sitemap does not equate published supporting records with standalone public
pages. A defensive hook also removes unpublished/noindex/utility/supporting
content even if an entity override requests inclusion. Review this policy if a
new content type or valuable taxonomy landing page is introduced.

The sitemap currently has exactly eight canonical production URLs. It is
regenerated by cron. Configure a reliable scheduled Drupal cron job on hosting.
Its base URL is explicitly set so CLI/staging generation cannot leak development
hostnames. Composer appends its declaration to the standard Drupal robots.txt
from `assets/robots.append.txt`; no custom replacement of core robots rules is
needed. Robots disallow rules are not used as a substitute for noindex.

## Structured data

Basic pages and the homepage describe the band as **MusicGroup**, with the
confirmed Instagram profile, public URL and logo. WebSite and appropriate page
types share stable entity IDs and publisher references. About uses AboutPage,
Book now ContactPage, and Videos/Repertoire/Testimonials CollectionPage. Future
Articles use Article markup, their visible title and actual publication/change
timestamps. Utility and error pages do not emit the content graph.

There is no invented address, service area, pricing, event, rating or review
score. Placeholder YouTube videos do not receive Deep Cuts VideoObject markup.
When final videos arrive, record accurate titles, descriptions, upload dates and
thumbnails, then consider VideoObject markup using current requirements.
Structured data does not guarantee a search feature or ranking improvement.

## Reproduction and deployment

`composer install` supplies the modules/libraries and reconstructs robots.txt.
The complete active configuration is exported to `sync/` for normal configuration
import. Node-specific metadata, focus phrases, selected images and sitemap entity
overrides are database content, not configuration export.

For initial construction on an existing project database:

```sh
ddev drush en metatag metatag_open_graph metatag_twitter_cards token redirect pathauto simple_sitemap schema_metatag schema_organization schema_web_site schema_web_page schema_article yoast_seo deepcuts_seo -y
ddev drush php:script scripts/setup-seo.php
ddev drush cr
ddev drush simple-sitemap:generate
ddev drush php:script scripts/verify-seo.php
```

`setup-seo.php` preserves nonempty editorial metadata, focus fields and existing
aliases. It reapplies technical defaults and sitemap policy, so use it as an
initial-build utility rather than a routine deployment hook. Normal deployments
use Composer, configuration import and the project's database/content workflow.

## Verification

`scripts/verify-seo.php` checks anonymous rendered titles/descriptions, singular
public canonicals, matching Open Graph/Twitter fields, parsed JSON-LD, utility and
404 exclusions, all eight sitemap URLs, fetchable sharing image dimensions,
canonical-path redirects and editor forms. It exercises public-host/HTTPS policy
in memory without enabling indexing in DDEV. Rolled-back fixtures check selected
sharing images, noindex sitemap exclusion, Article alias generation and automatic
permanent redirects after alias changes. It confirms Real-time SEO sees the
Recorded LIVE and Credits Paragraphs content.

The existing supporting-content route checks and Videos page checks also pass.
Public crawler fetches, actual social-platform previews, production redirect/TLS
configuration and Search Console remain launch checks requiring the deployed site.
