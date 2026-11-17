<?php

/**
 * @file
 * Verify About: ddev drush php:script scripts/verify-about-page.php
 */

$options = ['headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE];
$response = \Drupal::httpClient()->get('http://localhost/about', $options);
if ($response->getStatusCode() !== 200) { throw new \RuntimeException('About page failed.'); }
$html = (string) $response->getBody();
$dom = new \DOMDocument(); @$dom->loadHTML($html);
$xpath = new \DOMXPath($dom);
$cards = $xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " dc-member ")]');
if ($cards->length !== 4 || $xpath->query('//h1')->length !== 1) { throw new \RuntimeException('Expected four member cards and one H1.'); }
$data = json_decode(file_get_contents(__DIR__ . '/data/about.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach ($data['members'] as $index => $member) {
  $card = $cards->item($index);
  if (!str_contains($card->textContent, $member['bio']) || !str_contains($card->textContent, $member['role'])) { throw new \RuntimeException('Incomplete/out-of-order biography: ' . $member['name']); }
  if ($xpath->query('.//h3/a', $card)->length) { throw new \RuntimeException('Member name must not link to standalone page.'); }
}
$images = $xpath->query('//img[contains(@src, "/styles/dc_about_intro/") or contains(@src, "/styles/dc_member_portrait/")]');
if ($images->length !== 5) { throw new \RuntimeException('Expected five preset images.'); }
foreach ($images as $image) {
  $src = $image->getAttribute('src');
  $path = parse_url($src, PHP_URL_PATH);
  $query = parse_url($src, PHP_URL_QUERY);
  $response = \Drupal::httpClient()->get('http://localhost' . $path . ($query ? '?' . $query : ''), $options);
  $size = getimagesizefromstring((string) $response->getBody());
  $expected = str_contains($src, '/dc_about_intro/') ? [480, 450] : [420, 260];
  if ($response->getStatusCode() !== 200 || !$size || [$size[0], $size[1]] !== $expected || !$image->getAttribute('alt')) { throw new \RuntimeException('Image preset/alt verification failed: ' . $src); }
}
echo "About: HTTP 200; four complete, ordered biographies; no member-title links; five correctly sized image derivatives with alt text.\n";
