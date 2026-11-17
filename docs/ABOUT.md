# About page

The existing Basic page at `/about` has three sections:

1. **Image and text**: “What we do”, right-hand image on desktop, bordered,
   About introduction image preset and Packages button.
2. **Page breaker**: “Who we are”, pale lavender background.
3. **Content listing**: Band members, rendered by `deepcuts_band_member` and
   sorted by Display order, then node ID. Current order: Callum, Jack, Lee, Phil.

Edit the page to change its introduction or section settings. Edit individual
Band member nodes through Content administration to change names, roles,
biographies, portraits or ordering. Full biographies appear in cards. Individual
Band member canonical URLs show the branded 404, while edit forms remain subject
to normal Drupal permissions.

## Images

Five reusable Image Media items were imported from `_resources/Images/About`.
They are named `About: Deep Cuts together`, `About: Callum`, `About: Jack`,
`About: Lee` and `About: Phil`.

To replace a photo, edit its Media item in the Media library, replace the image,
update the alternative text and position the focal-point crosshair over the
important subject. Save the Media item. Larger originals can be uploaded;
Drupal generates the same displayed crop from the replacement file.

| Use | Image style / Media view mode | Output |
| --- | --- | --- |
| Introduction | `dc_about_intro` | 480 × 450 |
| Member portraits | `dc_member_portrait` | 420 × 260 |

Both styles use Focal Point Scale and Crop. Files matching the supplied crop
dimensions work as-is. Larger originals are scaled and cropped around their
focal point. Avoid smaller uploads: the effect enlarges undersized images.
The source files are retained; styling does not overwrite them. CSS scales the
derivatives down on smaller screens without changing their aspect ratio.

The About introduction preset is a choice on Image and text paragraphs; the
default Natural image ratio choice retains the existing general Media display.
The page hero remains a separate field using the shared responsive hero styles.

## Rebuilding and verification

After the existing content-model, page and paragraph setup scripts:

```sh
ddev drush php:script scripts/build-about-page.php
ddev drush cr
ddev drush php:script scripts/verify-about-page.php
ddev drush php:script scripts/verify-song-pages.php
```

The build preserves existing Media, Band member nodes and populated page
sections. Configuration is tracked in `sync/`; content and uploaded files are
database/files assets, recreated by the setup script on a fresh build with the
source images available.
