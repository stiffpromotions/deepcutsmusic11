# Packages page

`/packages` reuses the Basic page and its five Paragraph sections:

1. **Text**: bordered “Your entertainment package” introduction. The Full HTML
   editor holds two explicit `.dc-package-intro` columns and the quote button.
2. **Page breaker**: Styles introduction, pale purple.
3. **Content listing**: published Package nodes via `deepcuts_package`.
4. **Page breaker**: Upgrades & Add-ons introduction, pale purple.
5. **Content listing**: published Add-on nodes via `deepcuts_add_on`.

Edit the Basic page for introductory copy. Edit individual Package or Add-on
nodes for card text, images and Display order. The Views sort by Display order,
then node ID. Complete bodies are rendered rather than trimmed teasers. Cards
have no standalone title links; canonical requests show the site's branded 404.

Three Package nodes represent Electric, Acoustic and Roaming. Eleven Add-ons
represent the options in Figma, including the final Special requests card.
No numeric prices were supplied. Optional Price wording fields remain available.
Each newly created Package references the eleven Add-ons as available options;
edit those references as the offering changes.

## Images and presentation

The three reusable Image Media items are named `Packages: Electric`,
`Packages: Acoustic` and `Packages: Roaming`. Replace an image on its Media
editing form, update the alt text and set the focal-point crosshair. The
`dc_package_style` view mode applies a 307×260 Focal Point Scale and Crop style.
Larger originals can be uploaded and cropped automatically. Avoid smaller
images, which this effect would enlarge. Originals are not overwritten.

Card layouts use one column below 768px, two at 768–991px and three from 992px.
The final two Add-on cards share a desktop row when there are 3n+2 items. Hover
and keyboard focus within linked cards reveal the lavender/gold treatment.
Copy remains visible without hover or JavaScript. Styles live in
`themes/custom/deepcutsmusic11/scss/_paragraphs.scss`.

## Setup and verification

After the existing content-model, page and Paragraph setup:

```sh
ddev drush php:script scripts/build-packages-page.php
ddev drush cr
ddev drush php:script scripts/verify-packages-page.php
ddev drush php:script scripts/verify-song-pages.php
```

The build preserves existing named Media, Package/Add-on nodes and populated
page sections. Source copy lives in `scripts/data/packages.json` and
`packages-intro.html`. Configuration is exported to `sync/`; content and image
files must be deployed separately or recreated by the build with source images.
