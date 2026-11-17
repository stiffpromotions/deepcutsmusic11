<?php

/**
 * @file
 * Creates the site's initial pages and navigation without replacing content.
 *
 * Run: ddev drush php:script scripts/build-site-pages.php
 * Nodes, aliases, and menu links are content: configuration export excludes them.
 */

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\path_alias\Entity\PathAlias;

$pages = [
  'home' => ['Home', '/home', 'Deep Cuts. Live music for your event.'],
  'about' => ['About', '/about', 'Meet Deep Cuts and the musicians behind the music.'],
  'packages' => ['Packages', '/packages', 'Explore our live music packages and optional extras.'],
  'videos' => ['Videos', '/videos', 'Watch Deep Cuts perform live.'],
  'repertoire' => ['Repertoire', '/repertoire', 'Explore the Deep Cuts repertoire.'],
  'testimonials' => ['Testimonials', '/testimonials', 'Read what our clients say about Deep Cuts.'],
  'faqs' => ['FAQs', '/faqs', 'Answers to common questions about booking Deep Cuts.'],
  'book_now' => ['Book now', '/book-now', 'Get in touch to discuss Deep Cuts for your event.'],
  'privacy' => ['Privacy policy', '/privacy-policy', 'Privacy policy content is being prepared.'],
  'cookies' => ['Cookie policy', '/cookie-policy', 'Cookie policy content is being prepared.'],
  'thank_you' => ['Thank you', '/thank-you', 'Thank you for your enquiry.'],
  'error' => ['Mic drop…', '/error', 'We could not find that page. Please use the navigation to find your way back.'],
];
$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
$nodes = [];

// Check every planned alias before making changes; never take over another route.
foreach ($pages as $key => [$title, $alias, $intro]) {
  $aliases = $alias_storage->loadByProperties(['alias' => $alias]);
  foreach ($aliases as $existing_alias) {
    $path = $existing_alias->getPath();
    if (!preg_match('@^/node/(\d+)$@', $path, $matches)) {
      throw new RuntimeException("Alias $alias is already used by $path");
    }
    $node = Node::load($matches[1]);
    if (!$node || $node->bundle() !== 'page' || $node->label() !== $title) {
      throw new RuntimeException("Alias $alias is already used by a different page.");
    }
    $nodes[$key] = $node;
  }
}

foreach ($pages as $key => [$title, $alias, $intro]) {
  if (!isset($nodes[$key])) {
    $matches = $node_storage->loadByProperties(['type' => 'page', 'title' => $title]);
    if (count($matches) > 1) {
      throw new RuntimeException("Multiple pages titled $title need manual review.");
    }
    $node = $matches ? reset($matches) : Node::create([
      'type' => 'page', 'title' => $title, 'status' => TRUE, 'uid' => 1,
      'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
      'body' => ['value' => $intro, 'format' => 'plain_text'],
      'promote' => FALSE, 'sticky' => FALSE,
    ]);
    if ($node->isNew()) {
      $node->setRevisionLogMessage('Initial site structure; placeholder copy pending page build.');
      $node->save();
    }
    if (!$alias_storage->loadByProperties(['alias' => $alias])) {
      PathAlias::create([
        'path' => '/node/' . $node->id(), 'alias' => $alias,
        'langcode' => $node->language()->getId(),
      ])->save();
    }
    $nodes[$key] = $node;
  }
  echo $title . ': /node/' . $nodes[$key]->id() . ' -> ' . $alias . PHP_EOL;
}

$menus = [
  'main' => ['home', 'about', 'packages', 'videos', 'repertoire', 'testimonials', 'faqs', 'book_now'],
  'footer' => ['privacy', 'cookies'],
];
foreach ($menus as $menu => $keys) {
  foreach ($keys as $weight => $key) {
    $uri = 'entity:node/' . $nodes[$key]->id();
    $existing = \Drupal::entityTypeManager()->getStorage('menu_link_content')->loadByProperties([
      'menu_name' => $menu, 'link.uri' => $uri,
    ]);
    // Home already has a core menu link; the page node uses the site root.
    if ($key === 'home') {
      continue;
    }
    if (!$existing) {
      MenuLinkContent::create([
        'title' => $pages[$key][0], 'menu_name' => $menu,
        'link' => ['uri' => $uri], 'weight' => $weight,
        'enabled' => TRUE, 'expanded' => FALSE,
        'langcode' => $nodes[$key]->language()->getId(),
      ])->save();
    }
  }
}
\Drupal::configFactory()->getEditable('system.site')
  ->set('page.front', '/node/' . $nodes['home']->id())
  ->set('page.404', '/node/' . $nodes['error']->id())
  ->save();
echo "Front page, 404 page, main menu, and policy links configured.\n";
