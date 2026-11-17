# Deep Cuts content

Prevents standalone Song, Review, FAQ, Band member, Package and Add-on pages
while retaining entity access for listings.
The request subscriber runs after routing and parameter conversion and throws a
404 for those canonical routes, including aliases. Edit and other node-operation
routes retain normal Drupal permissions. No redirect is used.

Drupal's configured 404 page supplies the response body. Build that content with
`ddev drush php:script scripts/build-error-page.php` after the page/paragraph
setup. Enable with `ddev drush en deepcuts_content -y` and rebuild caches.

Verify with `ddev drush php:script scripts/verify-song-pages.php` in DDEV.
The script uses the local site hostname and checks real anonymous HTTP responses
as well as canonical/edit-route subscriber behaviour.
