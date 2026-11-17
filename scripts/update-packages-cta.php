<?php

/**
 * Move the existing quote CTA after both columns without replacing authored copy.
 * Run: ddev drush php:script scripts/update-packages-cta.php
 */

$path = \Drupal::service('path_alias.manager')->getPathByAlias('/packages');
if (!preg_match('@^/node/(\d+)$@', $path, $match)) { throw new \RuntimeException('Packages page missing.'); }
$page = \Drupal\node\Entity\Node::load($match[1]);
foreach ($page->get('field_sections')->referencedEntities() as $paragraph) {
  if (!$paragraph->hasField('field_text')) { continue; }
  $html = $paragraph->get('field_text')->value ?? '';
  if (!str_contains($html, 'dc-package-intro') || str_contains($html, 'dc-package-intro__cta')) { continue; }
  $button = '<p><a class="btn btn-secondary" href="/book-now">Request a quote</a></p>';
  if (substr_count($html, $button) !== 1) { throw new \RuntimeException('Expected one quote button; preserve edited content for review.'); }
  $updated = str_replace($button, '', $html);
  $end = strrpos($updated, '</div>');
  if ($end === FALSE) { throw new \RuntimeException('Introduction wrapper missing.'); }
  $updated = substr_replace($updated, str_replace('<p>', '<p class="dc-package-intro__cta">', $button) . "\n", $end, 0);
  $paragraph->set('field_text', ['value' => $updated, 'format' => $paragraph->get('field_text')->format]);
  $paragraph->setNewRevision(TRUE); $paragraph->save();
  $values = $page->get('field_sections')->getValue();
  foreach ($values as &$value) {
    if ($value['target_id'] == $paragraph->id()) { $value['target_revision_id'] = $paragraph->getRevisionId(); }
  }
  unset($value);
  $page->set('field_sections', $values)->setNewRevision(TRUE);
  $page->setRevisionLogMessage('Place quote CTA after all introduction copy on mobile.');
  $page->save();
  echo "Quote CTA moved; introduction copy preserved.\n";
}
