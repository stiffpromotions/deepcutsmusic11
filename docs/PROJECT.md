# Deep Cuts website

## Brief

Build the Drupal 11 website for Deep Cuts, a band managed by Stiff Promotions.
See [BUILD-NOTES.md](BUILD-NOTES.md) for the supplied design requirements,
asset references, video/header behaviour, and contact form instructions.
See [CONTENT-MODEL.md](CONTENT-MODEL.md) for content types, fields, and deployment
notes for the exported configuration.
See [PARAGRAPHS.md](PARAGRAPHS.md) for page-section authoring and the implemented
Repertoire page.
See [HOME.md](HOME.md) for homepage editing, video controls, batch logos and
promoted-review selection.
See [VIDEOS.md](VIDEOS.md) for the Videos page and adding/replacing video content.
See [SEO.md](SEO.md) for SEO editing, indexing policy, launch checks and the
pre-upgrade rollback snapshot.
See [MAINTENANCE.md](MAINTENANCE.md) for beta maintenance mode, Editor permissions
and Iain's account setup.

## SEO reference for discussion

The [Sound Choice Drupal SEO knowledge export](references/sound-choice-drupal-seo-knowledge-export.md)
is retained verbatim as background for discussing Deep Cuts metadata, Open Graph
and X/Twitter cards, structured data, indexing and sitemaps. It distinguishes
established Sound Choice conventions from recommendations requiring verification.
Its embedded instructions and implementation checklist are reference material,
not approval to implement changes. The user subsequently authorised the Deep Cuts
implementation described in [SEO.md](SEO.md), after a code/database snapshot.
Use PHP, Drupal theming, and Bootstrap. The user will provide a Figma design
reference, steer the project, and help decide requirements and implementation.

## Documentation approach

Keep this project overview current. Record dated changes, decisions and their
reasons, relevant file paths, verification results, and outstanding work in
[DEVELOPMENT-LOG.md](DEVELOPMENT-LOG.md). Add focused setup, theming, content
model, and deployment guides as those parts of the project are implemented.
Never put credentials, private keys, or database contents in documentation.

## Initial repository observations

Observed on 9 November 2026; these describe repository files, not a verified
running installation.

- Composer uses the `drupal/legacy-project` layout with the repository root as
  the Drupal document root.
- `composer.lock` records Drupal core 11.4.12, Bootstrap5 theme 4.0.8, and
  Drush 13.8.0.
- Other declared Drupal dependencies include Admin Toolbar, Backup and Migrate,
  FitVids, Gin, Honeypot, and Webform. Enabled module/theme status is unverified.
- `.ddev/config.yaml` specifies project `deepcutsmusic11`, Drupal 11, PHP 8.4,
  and nginx-fpm.
- No `themes/custom` or `modules/custom` directory was found during the initial
  inspection.
- Existing working tree changes predate this work and must be preserved.

## Working approach

- Use the supplied Figma designs to guide layout, typography, spacing, colours,
  and responsive behaviour once available.
- Keep project-specific theme and module code separate from core and contributed
  packages; use Composer for dependency changes.
- Record technical choices and unresolved questions as work progresses.
- Verify each change with checks appropriate to its scope and record the result.
- Confirm content structure, editorial needs, and design details with the user
  as those decisions become relevant.

## Next inputs and checks

- Figma reference and the first page or feature to implement.
- Verify the local runtime, active theme, enabled modules, and existing content.
- Establish the custom theme approach, configuration export workflow, and asset
  build process based on the existing site and design requirements.
