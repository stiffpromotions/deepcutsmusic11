<?php

/**
 * @file
 * Sets up footer menu content. Run: ddev drush php:script scripts/setup-footer.php
 * Optionally pass the confirmed Instagram URL via DEEPCUTS_INSTAGRAM_URL.
 */

use Drupal\menu_link_content\Entity\MenuLinkContent;

$links = [
  ['Contact', '/book-now', 0],
  ['FAQs', '/faqs', 1],
  ['Privacy Policy', '/privacy-policy', 2],
  ['Cookie Policy', '/cookie-policy', 3],
];
if ($instagram = getenv('DEEPCUTS_INSTAGRAM_URL') ?: 'https://www.instagram.com/deepcutsmusic/') {
  if (!preg_match('@^https://(www\.)?instagram\.com/[^\s]+$@', $instagram)) {
    throw new \InvalidArgumentException('Supply an HTTPS Instagram profile URL.');
  }
  $links[] = ['Instagram', $instagram, 4];
}
$links[] = ['Book now', '/book-now', 5];
$storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
foreach ($links as [$title, $path, $weight]) {
  $uri = str_starts_with($path, 'https://') ? $path : 'internal:' . $path;
  $existing = $storage->loadByProperties(['menu_name' => 'footer', 'title' => $title]);
  // Reuse existing node links, even if their original title differs in case.
  if (!$existing && str_starts_with($path, '/')) {
    $node_path = \Drupal::service('path_alias.manager')->getPathByAlias($path);
    $matches = $storage->loadByProperties(['menu_name' => 'footer', 'link.uri' => str_replace('/node/', 'entity:node/', $node_path)]);
    foreach ($matches as $candidate) {
      if (strcasecmp($candidate->label(), $title) === 0) {
        $existing = [$candidate];
        break;
      }
    }
  }
  $link = $existing ? reset($existing) : MenuLinkContent::create(['menu_name' => 'footer']);
  $options = $title === 'Book now' ? ['attributes' => ['class' => ['btn', 'btn-nav']]] : [];
  $link->set('title', $title)->set('link', ['uri' => $uri, 'options' => $options])
    ->set('weight', $weight)->set('enabled', TRUE)->save();
  echo "$title: $uri\n";
}
