<?php

/**
 * @file
 * Verify listing-node 404 handling: ddev drush php:script scripts/verify-song-pages.php
 */

use Drupal\deepcuts_content\EventSubscriber\SongPageSubscriber;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$ids = \Drupal::entityQuery('node')->condition('type', 'song')->condition('status', 1)->accessCheck(FALSE)->range(0, 1)->execute();
if (!$ids) { throw new \RuntimeException('Expected at least one published Song.'); }
$song = Node::load(reset($ids));
$review_ids = \Drupal::entityQuery('node')->condition('type', 'review')->condition('status', 1)->accessCheck(FALSE)->range(0, 1)->execute();
if (!$review_ids) { throw new \RuntimeException('Expected at least one published Review.'); }
$review = Node::load(reset($review_ids));
$faq_ids = \Drupal::entityQuery('node')->condition('type', 'faq')->condition('status', 1)->accessCheck(FALSE)->range(0, 1)->execute();
if (!$faq_ids) { throw new \RuntimeException('Expected at least one published FAQ.'); }
$faq = Node::load(reset($faq_ids));
$member_ids = \Drupal::entityQuery('node')->condition('type', 'band_member')->condition('status', 1)->accessCheck(FALSE)->range(0, 1)->execute();
if (!$member_ids) { throw new \RuntimeException('Expected at least one published Band member.'); }
$member = Node::load(reset($member_ids));
$offerings = [];
foreach (['package', 'add_on'] as $bundle) {
  $offering_ids = \Drupal::entityQuery('node')->condition('type', $bundle)->condition('status', 1)->accessCheck(FALSE)->range(0, 1)->execute();
  if (!$offering_ids) { throw new \RuntimeException("Expected a published $bundle."); }
  $offerings[] = Node::load(reset($offering_ids));
}
$subscriber = new SongPageSubscriber();
foreach ([$song, $review, $faq, $member, ...$offerings] as $node) {
foreach (['entity.node.canonical' => TRUE, 'entity.node.edit_form' => FALSE] as $route => $expect_404) {
  $request = new Request([], [], ['_route' => $route, 'node' => $node]);
  $event = new RequestEvent(\Drupal::service('http_kernel'), $request, HttpKernelInterface::MAIN_REQUEST);
  $not_found = FALSE;
  try { $subscriber->onRequest($event); }
  catch (NotFoundHttpException) { $not_found = TRUE; }
  if ($not_found !== $expect_404) { throw new \RuntimeException("Unexpected restriction on $route"); }
}
}
// Real anonymous requests check status, branded error rendering and listing.
foreach ([
  ['/node/' . $song->id(), 404, 'unexpected interlude'],
  ['/node/' . $review->id(), 404, 'unexpected interlude'],
  ['/node/' . $faq->id(), 404, 'unexpected interlude'],
  ['/node/' . $member->id(), 404, 'unexpected interlude'],
  ['/node/' . $offerings[0]->id(), 404, 'unexpected interlude'],
  ['/node/' . $offerings[1]->id(), 404, 'unexpected interlude'],
  ['/deepcuts-missing-page-check', 404, 'unexpected interlude'],
  ['/error', 200, 'unexpected interlude'],
  ['/repertoire', 200, htmlspecialchars($song->label(), ENT_QUOTES, 'UTF-8')],
  ['/testimonials', 200, htmlspecialchars($review->get('field_reviewer')->value, ENT_QUOTES, 'UTF-8')],
  ['/faqs', 200, htmlspecialchars($faq->label(), ENT_QUOTES, 'UTF-8')],
  ['/about', 200, htmlspecialchars($member->label(), ENT_QUOTES, 'UTF-8')],
  ['/packages', 200, 'Your entertainment package'],
] as [$path, $status, $text]) {
  $response = \Drupal::httpClient()->get('http://localhost' . $path, [
    'headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE,
    'allow_redirects' => FALSE,
  ]);
  if ($response->getStatusCode() !== $status || !str_contains((string) $response->getBody(), $text)) {
    throw new \RuntimeException("Unexpected status/content for $path: " . $response->getStatusCode());
  }
  echo "$path: $status, expected content present\n";
}
echo "All six supporting content types: canonical restrictions and unaffected editing routes verified.\n";
