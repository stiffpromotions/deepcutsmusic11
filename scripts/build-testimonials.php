<?php

/**
 * @file
 * Build Figma testimonials: ddev drush php:script scripts/build-testimonials.php
 * Existing reviews and page sections are preserved on subsequent runs.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\views\Entity\View;

$storage = FieldStorageConfig::loadByName('node', 'field_review_note');
if (!$storage) {
  FieldStorageConfig::create(['entity_type' => 'node', 'field_name' => 'field_review_note', 'type' => 'string_long', 'cardinality' => 1])->save();
}
if (!FieldConfig::loadByName('node', 'review', 'field_review_note')) {
  FieldConfig::create(['entity_type' => 'node', 'bundle' => 'review', 'field_name' => 'field_review_note', 'label' => 'Review attribution note', 'description' => 'Public context, such as a credit to the musicians’ previous band.'])->save();
}
$form = EntityFormDisplay::load('node.review.default');
$form->setComponent('field_review_note', ['type' => 'string_textarea', 'weight' => 4])->save();
foreach (['default', 'dc_card'] as $mode) {
  $display = EntityViewDisplay::load('node.review.' . $mode);
  $display->setComponent('field_review_note', ['type' => 'basic_string', 'label' => 'hidden', 'weight' => 103])->save();
}
$listing = FieldStorageConfig::loadByName('paragraph', 'field_listing');
$allowed = $listing->getSetting('allowed_values');
$allowed['review'] = 'Reviews';
$listing->setSetting('allowed_values', $allowed)->save();

// Keep the original featured-review default display for homepage use.
$view = View::load('deepcuts_review');
$displays = $view->get('display');
if (!isset($displays['all_reviews'])) {
  $filters = $displays['default']['display_options']['filters'];
  unset($filters['field_featured_value']);
  $displays['all_reviews'] = [
    'id' => 'all_reviews', 'display_title' => 'All published reviews',
    'display_plugin' => 'embed', 'position' => 1,
    'display_options' => ['defaults' => ['filters' => FALSE], 'filters' => $filters],
  ];
  $view->set('display', $displays)->save();
}
$data = json_decode(file_get_contents(__DIR__ . '/data/testimonials.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach ($data as $order => $review) {
  $existing = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'review', 'field_reviewer' => $review['reviewer']]);
  if ($existing) { continue; }
  Node::create([
    'type' => 'review', 'title' => $review['reviewer'], 'status' => TRUE,
    'uid' => 1, 'promote' => FALSE, 'field_display_order' => $order,
    'field_quote' => ['value' => '<p>“' . htmlspecialchars($review['quote'], ENT_QUOTES, 'UTF-8') . '”</p>', 'format' => 'basic_html'],
    'field_reviewer' => $review['reviewer'],
    'field_review_note' => !empty($review['previous_band']) ? 'Praise for Festival Nights – Lee, Callum and Jack’s previous band' : '',
  ])->save();
}
$path = \Drupal::service('path_alias.manager')->getPathByAlias('/testimonials');
if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($page = Node::load($match[1]))) {
  throw new \RuntimeException('Create the Testimonials page first.');
}
if ($page->get('field_sections')->isEmpty()) {
  $sections = [];
  foreach ([
    ['type' => 'content_listing', 'field_listing' => 'review'],
    ['type' => 'page_breaker', 'field_heading' => 'Want to read more?', 'field_background' => 'purple-50', 'field_text' => ['value' => '<p><a href="/book-now">Get in touch!</a> We’ve got 21 pages of glowing testimonials from all over the world.</p>', 'format' => 'basic_html']],
  ] as $values) {
    $p = Paragraph::create($values);
    $p->save();
    $sections[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
  }
  $page->set('field_sections', $sections);
  if ($page->get('field_hero_image')->isEmpty()) {
    // The approved homepage photo remains the placeholder until headers arrive.
    $repertoire_path = \Drupal::service('path_alias.manager')->getPathByAlias('/repertoire');
    if (preg_match('@^/node/(\d+)$@', $repertoire_path, $hero_match) && ($repertoire = Node::load($hero_match[1]))) {
      $page->set('field_hero_image', $repertoire->get('field_hero_image')->getValue());
    }
  }
  $page->setNewRevision(TRUE);
  $page->setRevisionLogMessage('Build Testimonials from Figma with eight reusable Reviews.');
  $page->save();
}

// Export only configuration owned by this build; menu/content changes stay intact.
foreach ([
  'field.storage.node.field_review_note', 'field.field.node.review.field_review_note',
  'core.entity_form_display.node.review.default', 'core.entity_view_display.node.review.default',
  'core.entity_view_display.node.review.dc_card', 'field.storage.paragraph.field_listing',
  'views.view.deepcuts_review',
] as $name) {
  file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
}
echo "Testimonials configured; eight Figma reviews imported without replacing existing content.\n";
