<?php

/**
 * @file
 * Verify information pages: ddev drush php:script scripts/verify-information-pages.php
 */

use Drupal\views\Views;

$faqs = json_decode(file_get_contents(__DIR__ . '/data/faqs.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach ($faqs as $group => $questions) {
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'faq_category', 'name' => $group]);
  $term = reset($terms);
  $view = Views::getView('deepcuts_faq');
  $view->setDisplay('default'); $view->setArguments([$term->id()]); $view->execute();
  foreach ($view->result as $row) {
    if ($row->_entity->get('field_faq_category')->target_id != $term->id() || !$row->_entity->isPublished()) {
      throw new \RuntimeException("Incorrect FAQ category/publication result for $group");
    }
  }
  if (count($view->result) !== count($questions)) { throw new \RuntimeException("Unexpected count for $group"); }
  echo "$group: " . count($view->result) . " published FAQs\n";
}
foreach (['faqs', 'privacy-policy', 'cookie-policy'] as $path) {
  $response = \Drupal::httpClient()->get('http://localhost/' . $path, ['headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE]);
  $html = (string) $response->getBody();
  if ($response->getStatusCode() !== 200) { throw new \RuntimeException("Failed /$path"); }
  $dom = new \DOMDocument();
  @$dom->loadHTML($html);
  $xpath = new \DOMXPath($dom);
  if ($xpath->query('//h1')->length !== 1) { throw new \RuntimeException("Expected single H1 on /$path"); }
  if ($path === 'faqs' && $xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " dc-faq ")]')->length !== 16) {
    throw new \RuntimeException('Expected 16 visible FAQ cards.');
  }
  if ($path === 'cookie-policy' && ($xpath->query('//table')->length !== 2 || $xpath->query('//table/caption')->length !== 2 || $xpath->query('//th[@scope="col"]')->length !== 7)) {
    throw new \RuntimeException('Expected two semantic cookie tables with headers/captions.');
  }
  echo "/$path: HTTP 200 and semantic structure OK\n";
}
