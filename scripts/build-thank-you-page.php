<?php

/**
 * @file
 * Build thank-you content: ddev drush php:script scripts/build-thank-you-page.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$path = \Drupal::service('path_alias.manager')->getPathByAlias('/thank-you');
if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($page = Node::load($match[1]))) {
  throw new \RuntimeException('Create the Thank you page using build-site-pages.php first.');
}
if ($page->get('field_sections')->isEmpty()) {
  $section = Paragraph::create([
    'type' => 'text', 'field_heading' => 'Music to our ears', 'field_bordered' => TRUE,
    'field_text' => [
      'value' => '<p>Thank you for getting in touch! We’re working on your request now and will come back to you within two working days. If you have any further requests in the meantime, you can <a href="/book-now">submit another form</a>, check out our <a href="/faqs">FAQs</a> or <a href="https://www.instagram.com/deepcutsmusic/">find us on our socials</a>.</p>',
      'format' => 'basic_html',
    ],
  ]);
  $section->save();
  $page->set('title', 'Thank you');
  $page->set('field_sections', [['target_id' => $section->id(), 'target_revision_id' => $section->getRevisionId()]]);
  if ($page->get('field_hero_image')->isEmpty()) {
    $hero_path = \Drupal::service('path_alias.manager')->getPathByAlias('/repertoire');
    if (preg_match('@^/node/(\d+)$@', $hero_path, $hero_match) && ($hero = Node::load($hero_match[1]))) {
      $page->set('field_hero_image', $hero->get('field_hero_image')->getValue());
    }
  }
  $page->setNewRevision(TRUE);
  $page->setRevisionLogMessage('Build Figma thank-you page ready for the future enquiry form confirmation.');
  $page->save();
}
echo "Thank-you page ready; existing sections preserved on repeat runs.\n";
$response = \Drupal::httpClient()->get('http://localhost/thank-you', [
  'headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE,
]);
$html = (string) $response->getBody();
if ($response->getStatusCode() !== 200 || !str_contains($html, 'Music to our ears') || !str_contains($html, 'within two working days')) {
  throw new \RuntimeException('Thank-you page response verification failed.');
}
echo "Verified HTTP 200 and confirmation content.\n";
