<?php

/**
 * @file
 * Verify section render variants and featured-review selection; roll back fixtures.
 * Run: ddev drush php:script scripts/verify-paragraphs.php && ddev drush cr
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\views\Views;

$transaction = \Drupal::database()->startTransaction();
try {
  $ids = [];
  foreach ([TRUE, FALSE] as $published) {
    $node = Node::create(['type' => 'review', 'title' => 'Section verification fixture', 'status' => $published, 'field_quote' => ['value' => 'Verification quote', 'format' => 'basic_html'], 'field_reviewer' => 'Verification reviewer', 'field_featured' => TRUE]);
    $node->save(); $ids[] = $node->id();
  }
  $view = Views::getView('deepcuts_review');
  $view->setDisplay('default'); $view->execute();
  $results = array_map(static fn ($r) => $r->_entity->id(), $view->result);
  if (!in_array($ids[0], $results) || in_array($ids[1], $results)) {
    throw new RuntimeException('Featured review publication filtering failed.');
  }
  $regular = Node::create(['type' => 'review', 'title' => 'Non-featured review fixture', 'status' => TRUE, 'field_quote' => ['value' => 'Ordinary published review', 'format' => 'basic_html'], 'field_reviewer' => 'Non-featured reviewer', 'field_featured' => FALSE]);
  $regular->save();
  $all = Views::getView('deepcuts_review');
  $all->setDisplay('all_reviews'); $all->execute();
  $all_ids = array_map(static fn ($r) => $r->_entity->id(), $all->result);
  if (!in_array($ids[0], $all_ids) || !in_array($regular->id(), $all_ids) || in_array($ids[1], $all_ids)) {
    throw new RuntimeException('All-review listing must include ordinary published reviews and exclude unpublished reviews.');
  }
  $media = \Drupal\media\Entity\Media::load(1);
  $package = Node::create(['type' => 'package', 'title' => 'Card verification fixture', 'status' => TRUE, 'body' => ['value' => 'Description', 'format' => 'basic_html'], 'field_inclusions' => ['value' => 'Inclusions', 'format' => 'basic_html']]);
  $package->save();
  $examples = [
    ['text', ['field_text' => ['value' => 'Text section', 'format' => 'basic_html'], 'field_columns' => '2']],
    ['image_text', ['field_text' => ['value' => 'Image text', 'format' => 'basic_html'], 'field_section_image' => $media->id(), 'field_image_position' => 'right']],
    ['card_group', ['field_items' => [$package->id()], 'field_columns' => '3']],
    ['reviews', ['field_items' => [$ids[0], $ids[1]], 'field_layout' => 'carousel']],
    ['reviews', ['field_layout' => 'grid']],
    ['page_breaker', ['field_text' => ['value' => 'Breaker', 'format' => 'basic_html'], 'field_link' => ['uri' => 'internal:/book-now', 'title' => 'Book now']]],
    ['content_listing', ['field_listing' => 'song']],
    ['content_listing', ['field_listing' => 'review']],
  ];
  foreach ($examples as [$type, $values]) {
    $p = Paragraph::create(['type' => $type, 'field_heading' => 'Section verification'] + $values);
    $build = \Drupal::entityTypeManager()->getViewBuilder('paragraph')->view($p);
    $html = (string) \Drupal::service('renderer')->renderInIsolation($build);
    if (!str_contains($html, 'dc-section')) { throw new RuntimeException("Failed render: $type"); }
    if ($type === 'reviews' && !empty($values['field_items']) && substr_count($html, '<figure') !== 1) { throw new RuntimeException('Selected reviews access filtering failed.'); }
    echo "$type: render OK\n";
  }
  echo "Featured and selected reviews exclude unpublished content.\n";
}
finally {
  $transaction->rollBack();
}
