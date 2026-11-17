<?php

/**
 * @file
 * Initial SEO setup: ddev drush php:script scripts/setup-seo.php
 * Seeds only empty editorial fields; existing overrides and aliases survive.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\image\Entity\ImageStyle;
use Drupal\metatag\Entity\MetatagDefaults;
use Drupal\node\Entity\Node;
use Drupal\pathauto\Entity\PathautoPattern;

// Compose a share card from the existing photograph and supplied logo.
$asset = DRUPAL_ROOT . '/themes/custom/deepcutsmusic11/images/social-share.jpg';
if (!file_exists($asset)) {
  if (!is_dir(dirname($asset))) { mkdir(dirname($asset), 0775, TRUE); }
  $photo = imagecreatefromjpeg(DRUPAL_ROOT . '/_resources/Images/Homepage/Homepage intro header.jpg');
  $card = imagecreatetruecolor(1200, 630);
  $source_width = imagesx($photo); $source_height = imagesy($photo);
  $crop_height = (int) round($source_width * 630 / 1200);
  imagecopyresampled($card, $photo, 0, 0, 0, max(0, (int) (($source_height - $crop_height) * .35)), 1200, 630, $source_width, $crop_height);
  imagefilledrectangle($card, 0, 0, 1199, 629, imagecolorallocatealpha($card, 36, 18, 65, 58));
  $logo = imagecreatefrompng(DRUPAL_ROOT . '/_resources/Logos/DEEPCUTS_CREAM_RGB.png');
  $height = 390; $width = (int) round(imagesx($logo) / imagesy($logo) * $height);
  imagecopyresampled($card, $logo, (int) ((1200 - $width) / 2), 120, 0, 0, $width, $height, imagesx($logo), imagesy($logo));
  imagejpeg($card, $asset, 90);
  imagedestroy($card); imagedestroy($photo); imagedestroy($logo);
}
if (!ImageStyle::load('deepcuts_social')) {
  $style = ImageStyle::create(['name' => 'deepcuts_social', 'label' => 'Social sharing (1200 × 630)']);
  $style->addImageEffect(['id' => 'focal_point_scale_and_crop', 'weight' => 0, 'data' => ['width' => 1200, 'height' => 630, 'crop_type' => 'focal_point']]);
  $style->save();
}
foreach (['page', 'article'] as $bundle) {
  foreach ([
    ['field_metatags', 'metatag', 'SEO and social metadata', [], [], 'Edit the SEO title and description here. Open Graph and X inherit them unless explicitly overridden.'],
    ['field_social_image', 'entity_reference', 'Social sharing image', ['target_type' => 'media'], ['handler' => 'default:media', 'handler_settings' => ['target_bundles' => ['image' => 'image']]], 'Optional: select an Image Media item. A 1200 × 630 focal-point crop is used; otherwise the branded sharing card is used.'],
    ['field_seo', 'yoast_seo', 'Editorial SEO guidance', [], [], 'Optional focus keyphrase and analysis. Scores are guidance, not publication requirements. Use Update analysis after editing page sections.'],
  ] as [$name, $type, $label, $storage_settings, $field_settings, $help]) {
    if (!FieldStorageConfig::loadByName('node', $name)) {
      FieldStorageConfig::create(['entity_type' => 'node', 'field_name' => $name, 'type' => $type, 'cardinality' => 1, 'settings' => $storage_settings])->save();
    }
    if (!FieldConfig::loadByName('node', $bundle, $name)) {
      FieldConfig::create(['entity_type' => 'node', 'bundle' => $bundle, 'field_name' => $name, 'label' => $label, 'description' => $help, 'settings' => $field_settings])->save();
    }
  }
  $form = EntityFormDisplay::load('node.' . $bundle . '.default');
  $form->setComponent('field_metatags', ['type' => 'metatag_firehose', 'weight' => 40]);
  $form->setComponent('field_social_image', ['type' => 'media_library_widget', 'weight' => 41]);
  $form->setComponent('field_seo', ['type' => 'yoast_seo_widget', 'weight' => 42, 'settings' => ['edit_title' => FALSE, 'edit_description' => FALSE, 'render_theme' => 'deepcutsmusic11', 'render_view_mode' => 'full']])->save();
  foreach (EntityViewDisplay::loadMultiple() as $display) {
    if ($display->getTargetEntityTypeId() === 'node' && $display->getTargetBundle() === $bundle) {
      foreach (['field_metatags', 'field_social_image', 'field_seo'] as $name) { $display->removeComponent($name); }
      $display->save();
    }
  }
}
$base = 'https://deepcutsmusic.co.uk';
$publisher = serialize(['@type' => 'MusicGroup', '@id' => $base . '/#band', 'name' => 'Deep Cuts']);
$defaults = [
  'global' => ['Global', [
    'title' => '[current-page:title] | [site:name]', 'robots' => 'noindex, follow', 'canonical_url' => '<none>', 'shortlink' => '<none>',
    'og_site_name' => '[site:name]', 'og_type' => 'website', 'og_locale' => 'en_GB', 'twitter_cards_type' => 'summary_large_image',
  ]],
  'node' => ['Content', ['title' => '[node:title] | [site:name]', 'description' => '[node:summary]', 'canonical_url' => '[node:url]', 'robots' => 'index, follow']],
  'node__page' => ['Content: Basic page', [
    'description' => 'Explore Deep Cuts live music, packages and repertoire, and get in touch to discuss your event.',
    'schema_organization_type' => 'MusicGroup', 'schema_organization_id' => $base . '/#band', 'schema_organization_name' => 'Deep Cuts',
    'schema_organization_url' => $base . '/', 'schema_organization_same_as' => 'https://www.instagram.com/deepcutsmusic/',
    'schema_organization_logo' => serialize(['@type' => 'ImageObject', 'url' => $base . '/themes/custom/deepcutsmusic11/logo.png']),
    'schema_web_site_type' => 'WebSite', 'schema_web_site_id' => $base . '/#website', 'schema_web_site_url' => $base . '/', 'schema_web_site_name' => 'Deep Cuts', 'schema_web_site_in_language' => 'en-GB', 'schema_web_site_publisher' => $publisher,
    'schema_web_page_type' => 'WebPage', 'schema_web_page_in_language' => 'en-GB', 'schema_web_page_publisher' => $publisher,
  ]],
  'node__article' => ['Content: Article', [
    'og_type' => 'article', 'schema_article_type' => 'Article', 'schema_article_headline' => '[node:title]',
    'schema_article_date_published' => '[node:created:custom:c]', 'schema_article_date_modified' => '[node:changed:custom:c]', 'schema_article_publisher' => $publisher,
    'schema_web_page_type' => 'WebPage', 'schema_web_page_in_language' => 'en-GB', 'schema_web_page_publisher' => $publisher,
  ]],
  'front' => ['Front page', ['canonical_url' => $base . '/', 'shortlink' => '<none>']],
  '403' => ['403 access denied', ['robots' => 'noindex, follow', 'canonical_url' => '<none>', 'shortlink' => '<none>']],
  '404' => ['404 page not found', ['robots' => 'noindex, follow', 'canonical_url' => '<none>', 'shortlink' => '<none>']],
  'user' => ['User', ['robots' => 'noindex, follow', 'canonical_url' => '<none>', 'shortlink' => '<none>']],
];
// Metatag's front-page defaults do not inherit the node bundle's schema tags.
$defaults['front'][1] += $defaults['node__page'][1];
$defaults['node__article'][1] += array_filter($defaults['node__page'][1], static fn ($key) => str_starts_with($key, 'schema_'), ARRAY_FILTER_USE_KEY);
foreach ($defaults as $id => [$label, $tags]) {
  $config = MetatagDefaults::load($id) ?: MetatagDefaults::create(['id' => $id, 'label' => $label]);
  $config->set('tags', $tags)->save();
}
$pages = json_decode(file_get_contents(__DIR__ . '/data/seo-pages.json'), TRUE, 512, JSON_THROW_ON_ERROR);
$alias_manager = \Drupal::service('path_alias.manager');
foreach ($pages as $alias => $values) {
  $path = $alias === '/' ? \Drupal::config('system.site')->get('page.front') : $alias_manager->getPathByAlias($alias);
  if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($node = Node::load($match[1]))) { throw new \RuntimeException('Missing page: ' . $alias); }
  $changed = FALSE;
  if ($node->get('field_metatags')->isEmpty()) {
    $tags = array_intersect_key($values, array_flip(['title', 'description', 'robots']));
    if (isset($values['schema'])) { $tags['schema_web_page_type'] = $values['schema']; }
    $node->set('field_metatags', json_encode($tags)); $changed = TRUE;
  }
  if (!empty($values['focus']) && $node->get('field_seo')->isEmpty()) {
    $node->set('field_seo', ['focus_keyword' => $values['focus']]); $changed = TRUE;
  }
  if ($changed) {
    $node->setNewRevision(TRUE); $node->setRevisionLogMessage('Seed page-specific SEO metadata and optional editorial focus keyphrase.'); $node->save();
  }
}
// Existing Basic page aliases remain manual; only future Articles use a pattern.
if (!PathautoPattern::load('deepcuts_articles')) {
  PathautoPattern::create(['id' => 'deepcuts_articles', 'label' => 'Deep Cuts articles', 'type' => 'canonical_entities:node', 'pattern' => 'news/[node:title]', 'selection_criteria' => ['article' => ['id' => 'entity_bundle:node', 'bundles' => ['article' => 'article'], 'negate' => FALSE, 'context_mapping' => ['node' => 'node']]], 'selection_logic' => 'and', 'weight' => 0])->save();
}
\Drupal::configFactory()->getEditable('redirect.settings')->set('auto_redirect', TRUE)->set('default_status_code', 301)->set('route_normalizer_enabled', TRUE)->save();
$generator = \Drupal::service('simple_sitemap.generator');
$generator->saveSetting('base_url', $base)->saveSetting('cron_generate', TRUE)->saveSetting('enabled_entity_types', ['node']);
$manager = $generator->entityManager()->setSitemaps(['default']);
foreach (\Drupal\node\Entity\NodeType::loadMultiple() as $bundle => $type) {
  $manager->setBundleSettings('node', $bundle, ['index' => in_array($bundle, ['page', 'article'], TRUE), 'priority' => 0.5, 'include_images' => FALSE]);
}
$generator->customLinkManager()->setSitemaps(['default'])->remove();
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'page']) as $node) {
  $manager->setEntityInstanceSettings('node', $node->id(), ['index' => \Drupal\deepcuts_seo\SeoPolicy::inSitemap($node), 'priority' => 0.5, 'include_images' => FALSE]);
}
$generator->rebuildQueue()->generate();
echo "SEO configured: editable metadata/sharing/analysis, MusicGroup/WebSite/WebPage schema, stable aliases, filtered production sitemap.\n";
