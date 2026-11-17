<?php

/**
 * @file
 * Verify SEO: ddev drush php:script scripts/verify-seo.php
 */

use Drupal\Core\Site\Settings;
use Drupal\deepcuts_seo\SeoPolicy;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$check = static function (bool $valid, string $message): void {
  if (!$valid) { throw new \RuntimeException($message); }
};
$get = static fn (string $path) => \Drupal::httpClient()->get('http://localhost' . $path, ['headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE, 'allow_redirects' => FALSE]);
$parse = static function ($response): array {
  $dom = new DOMDocument(); @$dom->loadHTML((string) $response->getBody());
  return [$dom, new DOMXPath($dom)];
};
$data = json_decode(file_get_contents(__DIR__ . '/data/seo-pages.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach ($data as $path => $expected) {
  $response = $get($path);
  $check($response->getStatusCode() === 200, 'Page failed: ' . $path);
  [$dom, $xpath] = $parse($response);
  $check($xpath->query('//head/title')->item(0)->textContent === $expected['title'], 'Title mismatch: ' . $path);
  $check($xpath->query('//meta[@name="description"]')->item(0)?->getAttribute('content') === $expected['description'], 'Description mismatch: ' . $path);
  $check($response->getHeaderLine('X-Robots-Tag') === 'noindex, follow', 'Development indexing leak: ' . $path);
  $check($xpath->query('//meta[@name="robots"]')->item(0)?->getAttribute('content') === 'noindex, follow', 'Development meta robots missing.');
  if (isset($expected['robots'])) {
    $check($xpath->query('//link[@rel="canonical"]')->length === 0, 'Utility page has canonical.');
    $check($xpath->query('//script[@type="application/ld+json"]')->length === 0, 'Utility page has schema.');
    continue;
  }
  $url = 'https://deepcutsmusic.co.uk' . $path;
  $check($xpath->query('//link[@rel="canonical"]')->length === 1, 'Duplicate/missing canonical: ' . $path);
  $check($xpath->query('//link[@rel="canonical"]')->item(0)->getAttribute('href') === $url, 'Incorrect canonical: ' . $path);
  foreach (['og:url' => $url, 'og:title' => $expected['title'], 'og:description' => $expected['description'], 'og:site_name' => 'Deep Cuts'] as $name => $value) {
    $check($xpath->query('//meta[@property="' . $name . '"]')->item(0)?->getAttribute('content') === $value, 'Incorrect ' . $name . ': ' . $path);
  }
  foreach (['twitter:card' => 'summary_large_image', 'twitter:title' => $expected['title'], 'twitter:description' => $expected['description']] as $name => $value) {
    $check($xpath->query('//meta[@name="' . $name . '"]')->item(0)?->getAttribute('content') === $value, 'Incorrect ' . $name . ': ' . $path);
  }
  $graph = json_decode($xpath->query('//script[@type="application/ld+json"]')->item(0)?->textContent ?? '', TRUE, 512, JSON_THROW_ON_ERROR);
  $entities = array_column($graph['@graph'], NULL, '@type');
  $check(isset($entities['MusicGroup'], $entities['WebSite'], $entities[$expected['schema'] ?? 'WebPage']), 'Missing schema entities: ' . $path);
  $check(($entities['MusicGroup']['logo']['url'] ?? '') === 'https://deepcutsmusic.co.uk/themes/custom/deepcutsmusic11/logo.png', 'Schema logo missing.');
  $check(($entities['WebSite']['publisher']['@id'] ?? '') === 'https://deepcutsmusic.co.uk/#band', 'Schema publisher is not an entity reference.');
  $check(!isset($entities['VideoObject'], $entities['Event'], $entities['AggregateRating']), 'Unsupported schema assertions.');
}
$image = $get('/themes/custom/deepcutsmusic11/images/social-share.jpg');
$size = getimagesizefromstring((string) $image->getBody());
$check($image->getStatusCode() === 200 && $size[0] === 1200 && $size[1] === 630, 'Sharing fallback not fetchable or wrong dimensions.');
$sitemap = $get('/sitemap.xml');
$check($sitemap->getStatusCode() === 200, 'Sitemap unavailable.');
$xml = simplexml_load_string((string) $sitemap->getBody());
$xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$urls = array_map('strval', $xml->xpath('//s:url/s:loc'));
$expected_urls = array_map(static fn ($path) => 'https://deepcutsmusic.co.uk' . $path, ['/', '/about', '/packages', '/videos', '/repertoire', '/testimonials', '/faqs', '/book-now']);
sort($urls); sort($expected_urls);
$check($urls === $expected_urls, 'Sitemap has missing, duplicate or unwanted URLs: ' . implode(', ', $urls));
$error = $get('/seo-verification-missing-page');
[$dom, $xpath] = $parse($error);
$check($error->getStatusCode() === 404 && $error->getHeaderLine('X-Robots-Tag') === 'noindex, follow', '404 response/indexing incorrect.');
$check($xpath->query('//link[@rel="canonical"]')->length === 0 && $xpath->query('//script[@type="application/ld+json"]')->length === 0, '404 advertises canonical/schema.');
$redirect = $get('/node/4');
$check($redirect->getStatusCode() === 301 && str_ends_with($redirect->getHeaderLine('Location'), '/videos'), 'Internal URL does not redirect to alias.');

// Exercise production behaviour in memory only; never turn indexing on in DDEV.
$original_settings = Settings::getAll();
new Settings(['deepcuts_seo_indexing_enabled' => TRUE] + $original_settings);
try {
  $request = Request::create('https://deepcutsmusic.co.uk/videos');
  $node = Node::load(4);
  $request->attributes->set('_route', 'entity.node.canonical'); $request->attributes->set('node', $node);
  $check(SeoPolicy::isPublicRequest($request), 'Production request rejected.');
  $check(!SeoPolicy::isPublicRequest(Request::create('https://staging.deepcutsmusic.uk/videos')), 'Staging accepted as production.');
  $subscriber = new \Drupal\deepcuts_seo\EventSubscriber\SeoResponseSubscriber();
  $event = new ResponseEvent(\Drupal::service('http_kernel'), $request, HttpKernelInterface::MAIN_REQUEST, new Response());
  $subscriber->onResponse($event);
  $check(!$event->getResponse()->headers->has('X-Robots-Tag'), 'Indexable production page blocked.');
  $request->attributes->set('node', Node::load(11));
  $event = new ResponseEvent(\Drupal::service('http_kernel'), $request, HttpKernelInterface::MAIN_REQUEST, new Response());
  $subscriber->onResponse($event);
  $check($event->getResponse()->headers->get('X-Robots-Tag') === 'noindex, follow', 'Thank-you page indexes on production.');
  $event = new RequestEvent(\Drupal::service('http_kernel'), Request::create('http://www.deepcutsmusic.co.uk/videos?test=1'), HttpKernelInterface::MAIN_REQUEST);
  $subscriber->onRequest($event);
  $check($event->getResponse()?->getStatusCode() === 301 && $event->getResponse()->headers->get('Location') === 'https://deepcutsmusic.co.uk/videos?test=1', 'Production HTTPS/hostname redirect failed.');
}
finally { new Settings($original_settings); }

\Drupal::service('account_switcher')->switchTo(\Drupal\user\Entity\User::load(1));
$transaction = \Drupal::database()->startTransaction();
try {
  $node = Node::load(4);
  $form = \Drupal::service('entity.form_builder')->getForm($node);
  $check(isset($form['field_metatags'], $form['field_social_image'], $form['field_seo']), 'SEO editor controls missing.');
  $preview = \Drupal::service('yoast_seo.entity_analyser')->createEntityPreview($node, 'deepcutsmusic11', 'full');
  $check(str_contains($preview['text'], 'Recorded LIVE') && str_contains($preview['text'], 'Credits'), 'Real-time SEO misses Paragraphs content.');
  // Exercise image selection and sitemap filtering without leaving fixtures.
  $images = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle' => 'image']);
  $media = reset($images);
  $node->set('field_social_image', $media->id());
  $tags = ['title' => 'Example', 'description' => 'Example description']; $context = ['entity' => $node];
  deepcuts_seo_metatags_alter($tags, $context);
  $check(str_contains($tags['og_image'], '/styles/deepcuts_social/') && $tags['twitter_cards_image'] === $tags['og_image'], 'Selected sharing image not used.');
  $fixture = Node::create(['type' => 'page', 'title' => 'SEO test', 'status' => 1, 'uid' => 1, 'field_metatags' => json_encode(['robots' => 'noindex, follow'])]); $fixture->save();
  $links = [['url' => SeoPolicy::canonical($fixture), 'meta' => ['entity_info' => ['entity_type' => 'node', 'id' => $fixture->id()]]]];
  deepcuts_seo_simple_sitemap_links_alter($links, NULL);
  $check(!$links, 'Noindex content leaks into sitemap.');
  $article = Node::create(['type' => 'article', 'title' => 'Deep Cuts SEO fixture', 'status' => 0, 'uid' => 1, 'path' => ['pathauto' => 1]]);
  $article->save();
  $generated_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $article->id());
  $check($generated_alias === '/news/deep-cuts-seo-fixture', 'Article Pathauto pattern failed: ' . $generated_alias);
  \Drupal::entityTypeManager()->getStorage('node')->resetCache([$article->id()]);
  $article = Node::load($article->id());
  $path_value = $article->get('path')->getValue();
  $path_value[0]['alias'] = '/news/deep-cuts-seo-fixture-renamed';
  $path_value[0]['pathauto'] = 0;
  $article->set('path', $path_value);
  $article->save();
  $redirects = \Drupal::entityTypeManager()->getStorage('redirect')->loadByProperties(['redirect_source.path' => 'news/deep-cuts-seo-fixture']);
  $check(count($redirects) === 1 && (int) reset($redirects)->getStatusCode() === 301, 'Alias change did not create a permanent redirect; matches: ' . count($redirects));
}
finally {
  $transaction->rollBack();
  \Drupal::service('account_switcher')->switchBack();
  \Drupal::entityTypeManager()->getStorage('node')->resetCache();
}
echo "SEO verified: metadata/social tags and JSON-LD on ten public pages; utility/error exclusions; eight canonical sitemap URLs; 1200×630 sharing image; alias/HTTPS redirects; environment protection; editor controls; Paragraphs analysis; image override and noindex sitemap safeguards.\n";
