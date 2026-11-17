<?php

/**
 * @file
 * Verify Packages: ddev drush php:script scripts/verify-packages-page.php
 */

$options = ['headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE];
$response = \Drupal::httpClient()->get('http://localhost/packages', $options);
if ($response->getStatusCode() !== 200) { throw new \RuntimeException('Packages page failed.'); }
$dom = new \DOMDocument(); @$dom->loadHTML((string) $response->getBody());
$xpath = new \DOMXPath($dom);
$cta = $xpath->query('//div[@class="dc-package-intro"]/p[@class="dc-package-intro__cta"]');
if ($cta->length !== 1 || $xpath->query('preceding-sibling::div', $cta->item(0))->length !== 2) {
  throw new \RuntimeException('Quote CTA must follow both introduction columns.');
}
$data = json_decode(file_get_contents(__DIR__ . '/data/packages.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach (['styles' => 'package', 'add_ons' => 'add-on'] as $key => $class) {
  $cards = $xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " dc-offering--' . $class . ' ")]');
  if ($cards->length !== count($data[$key])) { throw new \RuntimeException("Unexpected $key count."); }
  foreach ($data[$key] as $index => $item) {
    $card = $cards->item($index);
    $text = html_entity_decode(strip_tags($item['body']), ENT_QUOTES, 'UTF-8');
    if (!str_contains($card->textContent, $text) || trim($xpath->query('.//h3', $card)->item(0)->textContent) !== $item['title'] || $xpath->query('.//h3/a', $card)->length) {
      throw new \RuntimeException('Incomplete/out-of-order/linked card: ' . $item['title']);
    }
  }
}
$images = $xpath->query('//img[contains(@src, "/styles/dc_package_style/")]');
if ($images->length !== 3 || $xpath->query('//h1')->length !== 1) { throw new \RuntimeException('Expected three styled images and one H1.'); }
foreach ($images as $image) {
  $src = $image->getAttribute('src'); $path = parse_url($src, PHP_URL_PATH); $query = parse_url($src, PHP_URL_QUERY);
  $response = \Drupal::httpClient()->get('http://localhost' . $path . ($query ? '?' . $query : ''), $options);
  $size = getimagesizefromstring((string) $response->getBody());
  if ($response->getStatusCode() !== 200 || !$size || [$size[0], $size[1]] !== [307, 260] || !$image->getAttribute('alt')) { throw new \RuntimeException('Package derivative/alt check failed.'); }
}
echo "Packages: HTTP 200, three complete style cards, eleven complete add-ons, unlinked titles and three 307×260 image derivatives verified.\n";
