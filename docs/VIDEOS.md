# Videos

The `/videos` Basic page uses ordered **Video** page sections, with Electric and
Acoustic initially using the two supplied YouTube placeholders. The first URL is
Electric and the second is Acoustic. The existing shared hero image is retained
until final media arrives. Players are lazy-loaded, have accessible titles and
use a responsive 16:9 layout.

The page follows the Figma `/videos` design: bordered **Recorded LIVE** introduction,
Electric and Acoustic on white with left-aligned headings, then a full-width
pale-lavender **Credits** section. Desktop content has a 1200px maximum width and
80px section spacing, reducing on smaller screens. Introduction and Credits use
the existing editable Text and Page breaker sections. Their initial supplied
copy is recorded in `scripts/data/videos.json`.

## Editing

1. Edit Videos under **Content**, or visit `/node/4/edit` on the current database.
2. Expand a Video section to change its heading, background or selected Media.
3. To replace a YouTube URL, edit the corresponding Remote video under
   **Content → Media**, change its Video URL and save. The seeded items are named
   **Videos: Electric** and **Videos: Acoustic**. Media edits also affect other
   places using that same Media item.
4. To add another video, add a **Video** page section, enter its heading and use
   the Media Library to select or create a Remote video. Uploaded Video Media
   is also supported. Drag page sections to reorder them and save the page.
5. Remove a section to stop showing that video here. Its Media remains available
   for reuse. Unpublished Media is excluded by Drupal's entity access checks.

There is no fixed two-video limit or automatic listing of unrelated Media.

## Reproduction and deployment

Run `ddev drush php:script scripts/build-videos-page.php`, followed by
`ddev drush cr`. The build utility creates the Video paragraph configuration,
allows it on Basic pages and seeds the two sections only when the Videos page
has no sections. Existing Media edits and page sections are preserved on rerun.
It exports the affected configuration to `sync/` and runs the Figma content update.
For a site already built with the earlier two-video layout, run
`ddev drush php:script scripts/update-videos-figma.php`, followed by `ddev drush cr`.
This adds the missing introduction and Credits and changes the old video panels
to white. Once those sections exist, rerunning preserves editorial changes and
does not create duplicate sections. Existing video Media and URLs are retained.

On deployment, import configuration as usual and transfer database content through
the project's content/database workflow. Media and Paragraph instances are not
included in configuration export. The build utility is for initial setup rather
than overwriting subsequent editorial changes.

Verification: `ddev drush php:script scripts/verify-videos-page.php` checks the
initial seeded page's HTTP response, all four sections and supplied copy, bordered
intro/section colours, accessible lazy-loaded iframes, both Drupal oEmbed endpoints
and the Video editor form. Update its
seed-specific expectations after replacing or adding videos.

The Figma reference was read in the Figma desktop app. Safari desktop and 375px
responsive previews were checked for introduction, player sizing/alignment and
Credits wrapping. Both YouTube players load their thumbnails and controls;
full playback has not been tested in this update.
