<?php

/**
 * @file
 * Build Figma's error page: ddev drush php:script scripts/build-error-page.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;

$path = \Drupal::service('path_alias.manager')->getPathByAlias('/error');
$page = preg_match('@^/node/(\d+)$@', $path, $match) ? Node::load($match[1]) : NULL;
if (!$page) {
  $page = Node::create(['type' => 'page', 'title' => 'Mic drop…', 'status' => TRUE, 'uid' => 1, 'promote' => FALSE]);
  $page->save();
  PathAlias::create(['path' => '/node/' . $page->id(), 'alias' => '/error', 'langcode' => $page->language()->getId()])->save();
}
if ($page->get('field_sections')->isEmpty()) {
  $section = Paragraph::create([
    'type' => 'text', 'field_heading' => '... but not how we intended',
    'field_bordered' => TRUE,
    'field_text' => [
      'value' => '<p>We’re sorry that this page has taken an unexpected interlude. You can go back and try again, head to the <a href="/">homepage</a>, or <a href="/book-now">contact us via our form</a>.</p>',
      'format' => 'basic_html',
    ],
  ]);
  $section->save();
  $page->set('title', 'Mic drop…');
  $page->set('field_sections', [['target_id' => $section->id(), 'target_revision_id' => $section->getRevisionId()]]);
  if ($page->get('field_hero_image')->isEmpty()) {
    $repertoire_path = \Drupal::service('path_alias.manager')->getPathByAlias('/repertoire');
    if (preg_match('@^/node/(\d+)$@', $repertoire_path, $hero_match) && ($repertoire = Node::load($hero_match[1]))) {
      $page->set('field_hero_image', $repertoire->get('field_hero_image')->getValue());
    }
  }
  $page->setNewRevision(TRUE);
  $page->setRevisionLogMessage('Build Figma error page with bordered message and hero placeholder.');
  $page->save();
}
\Drupal::configFactory()->getEditable('system.site')->set('page.404', '/node/' . $page->id())->save();
foreach (['system.site', 'core.extension'] as $name) {
  file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
}
echo "Error page configured at /error (node {$page->id()}); existing sections preserved.\n";
