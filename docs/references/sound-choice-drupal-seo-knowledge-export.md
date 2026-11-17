# Sound Choice Drupal SEO Knowledge Export

Version: 1.0 | Source project: soundchoice.co.uk | Prepared: 16 November 2026

## Purpose and evidence levels

Portable knowledge for a separate Drupal build. **Established** means a decision or working convention recovered from Sound Choice project discussions. **Recommended / verify** means a sensible implementation pattern, not proof that an exact setting was deployed. Adapt branding, content types, URLs and search intent to the destination site.

## 1. Core philosophy (established)

- Build for humans and search intent first. Yoast is editorial guidance, not the final authority.
- Do not force green scores by repeating keywords, bloating copy or using unnatural sentences.
- Accept amber or red Yoast indicators when they reflect a legitimate editorial decision, especially on utility or editorial pages.
- Use a genuinely relevant, page-specific focus keyphrase, not a generic or artificial phrase.
- Distinctive, useful content and evidence of real work outperform thin, mass-produced location pages.
- Write descriptive internal links when they genuinely help navigation and topical relationships.

## 2. Drupal stack and responsibility split (established architecture)

- Drupal 11 with a custom theme (`soundchoice11` in the source project).
- Metatag: technical metadata defaults and content-type overrides.
- Yoast / Real-time SEO: editor-facing focus keyphrase and content analysis for Artist, Blog and Landing content.
- Pathauto: readable URL aliases.
- Redirect: preserve traffic and link equity when aliases change.
- Simple XML Sitemap: expose indexable canonical URLs for discovery.
- Schema.org structured data: structured meaning for appropriate content.
- Webform: conversion forms, not an SEO module.
- Image optimisation and internal linking are integral to publishing.

**Rule:** Metatag owns technical defaults. Yoast guides copy. Avoid two competing sources of truth for title, description and canonical tags.

## 3. Metatag defaults and indexing (established decisions)

| Page type | Established direction |
|---|---|
| Homepage | Bespoke metadata, `index,follow` |
| Normal published content | Canonical URL based on `[node:url]` |
| Artist | Bundle-specific metadata overrides |
| Blog | Bundle-specific metadata overrides |
| Landing | Bundle-specific metadata overrides |
| 403 / 404 pages | `noindex,follow` |
| User pages | `noindex,follow` |
| Taxonomy term pages | `noindex,follow` in Sound Choice's original configuration |

**Migration warning:** Do not copy taxonomy `noindex` blindly if the destination website uses valuable, editorially developed taxonomy landing pages. Review the indexing strategy per bundle and route.

### Recommended configuration checklist (verify in destination)

1. Configure global title and description patterns, then bundle-specific patterns in Metatag.
2. Override homepage metadata manually.
3. Use a single absolute, self-referencing canonical URL for indexable pages, with the intended public hostname and HTTPS scheme.
4. Verify whether `[node:url]` resolves to the clean alias and correct canonical hostname in the new site's actual configuration.
5. Ensure redirects from obsolete aliases are permanent where appropriate; avoid redirect chains.
6. Keep unpublished, staging, search-result and other non-public routes out of the sitemap and search index as appropriate.
7. Check robots meta directives in rendered HTML, not just configuration forms.
8. Do not assume robots.txt disallow is equivalent to `noindex`.

## 4. Yoast / Real-time SEO editorial workflow (established)

Use a manually chosen focus keyphrase that matches the page's actual subject and likely user intent. Incorporate it naturally, where appropriate, in:

- SEO title, ideally near the beginning when that reads well.
- Meta description, written to earn an appropriate click.
- Opening paragraph.
- A meaningful H2 or other heading.
- Readable URL alias.
- Main content, without mechanical repetition.

Specific established lessons:

- Shorten overlong titles, descriptions and slugs when clarity benefits.
- Ignore stop-word warnings if removing ordinary words would damage the URL or prose.
- Treat Flesch readability and minimum-word-count warnings as diagnostics, not quotas.
- Do not add filler solely to exceed a 300-word threshold.
- Do not insert headings solely to satisfy Yoast if the content does not need them.
- Use relevant internal links when a page genuinely benefits from them.
- For artist news, favour artist-specific phrases over fabricated SEO targets.
- Yoast focus keyphrases are not the same thing as internal site-search keywords or taxonomy terms.

### Editorial QA before publishing

Confirm unique intent; useful title and H1; compelling introduction; accurate, non-duplicated meta title/description; clean alias; helpful headings; relevant internal links; image alt text; mobile readability; working calls to action; correct indexing and canonical; accurate social preview.

## 5. Content and internal-link architecture (established)

Sound Choice's discovery model was:

**EVENT → EXPERIENCE → ARTIST → ADVICE**

Example: a wedding or corporate event page introduces an entertainment experience, links to a relevant act, and supports the decision with a useful article or case study. This is a conceptual model, not a mandatory Drupal taxonomy hierarchy.

- Commercial landing pages address distinct booking intent.
- Artist pages explain the act's real distinguishing features, lineup, repertoire, production and suitability.
- Blog/news pages supply useful evidence, announcements, advice and real-gig context.
- Avoid near-duplicate pages that merely swap location or keyword names.
- Use contextual, descriptive anchors rather than generic “click here”.
- Add internal links during content creation and revisit older content when new relevant pages appear.
- Maintain an **INTERNAL LINKING NOTES** section in editorial handoffs, identifying existing pages that should link to the new content.
- Preserve each page's distinct search intent and avoid overlinking.

### Sound Choice-specific conventions (do not transplant blindly)

- Editorial for commercial landing pages is delivered as one continuous HTML block, followed by automatically rendered artist cards.
- Artist recruitment CTA: `/join`.
- Artist-specific enquiries: `/enquire`.
- `/contact` is not a generic substitute for every CTA.
- Homepage H1 was “Find the perfect live act for your event”; related “live bands” phrasing belongs naturally in supporting copy and metadata.
- Sound Choice is a carefully curated live-music platform, not a mass artist or events directory.

## 6. Social sharing: Open Graph and X/Twitter cards

**Established:** social sharing metadata was part of the Metatag-centred approach. **Not recovered:** exact deployed token patterns, image styles, card types and field mappings. Revalidate rather than assume they were implemented exactly as below.

Recommended destination implementation:

- Configure Open Graph `og:title`, `og:description`, `og:type`, `og:url`, `og:image` and `og:site_name` using Metatag defaults and per-bundle overrides.
- Set an appropriate X/Twitter card type (usually `summary_large_image` for image-led pages) with matching title, description and image.
- Prefer an absolute HTTPS image URL to a publicly accessible, representative image. Check crop, aspect ratio, dimensions and file size against the sharing platform's current requirements.
- Provide a deliberate fallback sharing image when the content has no suitable image.
- Keep title and description meaningful even when a platform ignores one or both fields.
- Test the actual rendered HTML and real shared URL. Social platforms may cache previews, so allow for refresh/re-scrape behaviour.
- Check that canonical and `og:url` refer to the intended public page and that robots/security controls do not block image fetches.

## 7. Schema.org / structured data

**Established:** structured data was included in the intended technical SEO stack. **Not recovered:** exact module, JSON-LD templates, entities, properties or deployed schema types.

Recommended approach:

- Select schema types from what the content genuinely represents, rather than adding rich-result markup indiscriminately.
- Use Organisation/WebSite and suitable WebPage or Article markup where warranted.
- Use Event markup only for genuine event listings with accurate event details; do not mark generic artist profiles as events.
- Do not invent ratings, reviews, availability, prices or performances.
- Ensure JSON-LD agrees with visible page content and canonical URLs.
- Validate using Google's Rich Results Test where eligible, and Schema.org validation for general syntax.
- Review Google's current supported rich results and policies for the new site's content types.

## 8. URL, sitemap and search visibility

- Choose a single canonical hostname and enforce HTTPS consistently.
- Use short, readable, stable Pathauto aliases, with manual overrides when appropriate.
- Redirect previous aliases after changes; test for loops and chains.
- Ensure Simple XML Sitemap includes only appropriate canonical, public, indexable routes.
- Check sitemap output, robots.txt, meta robots and HTTP responses together.
- Verify Search Console ownership, sitemap submission and indexing status in the new project (recommended, not evidence of prior deployment).
- Use Search Console performance data to improve pages based on actual queries and click-through behaviour, rather than treating Yoast as ranking evidence.

## 9. Images and sharing

- Use original, relevant images where possible, particularly for acts and actual performances.
- Provide meaningful alt text for informative images; avoid keyword stuffing.
- Optimise image delivery and responsive sizes for page performance.
- Treat the hero image and the social-sharing image as related but not necessarily identical crops.
- Verify the image is publicly fetchable by crawlers and preview bots.

## 10. Reusable workflow for a new Drupal build

### Technical setup

1. Inventory Drupal version, installed SEO modules and content bundles.
2. Establish canonical domain, HTTPS and redirects.
3. Configure Metatag global defaults and content-bundle overrides.
4. Define index/noindex policy per route and bundle.
5. Configure Pathauto and Redirect.
6. Configure Simple XML Sitemap and verify its output.
7. Configure Open Graph and X/Twitter metadata with tested image fallbacks.
8. Add accurate structured data appropriate to content.
9. Verify output in rendered page source, not only in Drupal admin.

### Editorial setup

1. Define distinct search intent for each content type.
2. Create editorial field and publishing guidance for title, H1, meta title, description, focus keyphrase, alias, headings and imagery.
3. Use Yoast as a diagnostic, not a target score.
4. Build contextual internal links and maintain retrospective link notes.
5. QA social previews, canonicals, robots and structured data on representative pages.
6. Review actual search performance periodically and revise useful content accordingly.

## 11. What to verify before claiming parity with Sound Choice

The available project recollections do **not** establish exact installed module versions, Metatag token strings beyond `[node:url]`, precise Open Graph/Twitter field mappings, the schema implementation, Search Console configuration, image style dimensions, or the final deployed settings for every content type. Audit the source site's configuration export or rendered pages if exact reproduction is required.

## 12. Suggested instruction to paste into another project

> Use the Sound Choice Drupal SEO approach as the starting standard. Metatag owns technical metadata and defaults; Yoast / Real-time SEO provides editorial guidance, never rigid score targets. Configure canonical HTTPS URLs, intentional indexing, readable Pathauto aliases, Redirect and XML sitemap support. Include appropriate Open Graph/X sharing metadata and accurate Schema.org structured data. Write distinctive content around real user search intent, use a natural page-specific focus keyphrase, and add selective descriptive internal links. Never pad copy, force keyword density, fabricate schema facts or create thin location pages. Distinguish confirmed configuration from recommendations, and adapt all site-specific URLs, content bundles and indexing rules to the new build.
