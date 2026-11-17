<?php

/**
 * @file
 * Verify Videos: ddev drush php:script scripts/verify-videos-page.php
 */

$response = \Drupal::httpClient()->get('http://localhost/videos', ['headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE]);
if ($response->getStatusCode() !== 200) { throw new \RuntimeException('Videos page failed.'); }
$dom = new \DOMDocument();
@$dom->loadHTML((string) $response->getBody());
$xpath = new \DOMXPath($dom);
$headings = [];
foreach ($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " dc-videos ")]//h2') as $heading) {
  $headings[] = trim($heading->textContent);
}
if ($headings !== ['Recorded LIVE', 'Electric', 'Acoustic', 'Credits']) { throw new \RuntimeException('Figma sections missing or out of order.'); }
$data = json_decode(file_get_contents(__DIR__ . '/data/videos.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach ([$data['intro'], $data['credits_intro'], ...$data['credits']] as $copy) {
  if (!str_contains($dom->textContent, $copy)) { throw new \RuntimeException('Missing Figma copy: ' . $copy); }
}
$intro = $xpath->query('//section[contains(@class, "dc-section--bordered")]');
$credits = $xpath->query('//section[contains(@class, "dc-section--page-breaker") and contains(@class, "dc-section--bg-purple-50")]');
if ($intro->length !== 1 || $credits->length !== 1) { throw new \RuntimeException('Missing bordered intro or lavender Credits.'); }
$sections = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " dc-section--video ")]');
if ($sections->length !== 2 || $xpath->query('//h1')->length !== 1) { throw new \RuntimeException('Expected two video sections and one H1.'); }
foreach (['Electric' => 'uzZFKqJkdME', 'Acoustic' => 'khAwa7z6C_Q'] as $index => $id) {
  $section = $sections->item($index === 'Electric' ? 0 : 1);
  if (!str_contains($section->getAttribute('class'), 'dc-section--bg-white')) { throw new \RuntimeException('Video background must match Figma white.'); }
  if (trim($xpath->query('.//h2', $section)->item(0)?->textContent ?? '') !== $index) { throw new \RuntimeException('Incorrect video heading/order.'); }
  $iframe = $xpath->query('.//iframe', $section)->item(0);
  if (!$iframe || !str_contains(urldecode($iframe->getAttribute('src')), $id) || $iframe->getAttribute('loading') !== 'lazy' || !$iframe->getAttribute('title')) {
    throw new \RuntimeException('Missing accessible lazy-loaded player: ' . $index);
  }
  $src = $iframe->getAttribute('src');
  $player = \Drupal::httpClient()->get($src, ['verify' => FALSE, 'http_errors' => FALSE]);
  if ($player->getStatusCode() !== 200 || !str_contains((string) $player->getBody(), $id)) { throw new \RuntimeException('oEmbed player endpoint failed.'); }
}
$account = \Drupal::currentUser();
\Drupal::service('account_switcher')->switchTo(\Drupal\user\Entity\User::load(1));
try {
  $paragraph = \Drupal\paragraphs\Entity\Paragraph::create(['type' => 'video']);
  $form = \Drupal::service('entity.form_builder')->getForm($paragraph);
  if (!isset($form['field_video'], $form['field_heading'])) { throw new \RuntimeException('Video editing form missing fields.'); }
}
finally { \Drupal::service('account_switcher')->switchBack(); }
echo "Videos: HTTP 200, one H1, four ordered Figma sections with complete copy, bordered intro, white videos, lavender Credits, unchanged accessible YouTube embeds and editable Media form.\n";
