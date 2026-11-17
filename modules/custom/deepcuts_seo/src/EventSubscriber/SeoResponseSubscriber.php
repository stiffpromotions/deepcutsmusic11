<?php

declare(strict_types=1);

namespace Drupal\deepcuts_seo\EventSubscriber;

use Drupal\Core\Site\Settings;
use Drupal\deepcuts_seo\SeoPolicy;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applies environment protection even to cached and non-HTML responses.
 */
final class SeoResponseSubscriber implements EventSubscriberInterface {

  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest() || !Settings::get('deepcuts_seo_indexing_enabled', FALSE)) { return; }
    $request = $event->getRequest();
    $host = parse_url(SeoPolicy::baseUrl(), PHP_URL_HOST);
    if (in_array($request->getHost(), [$host, 'www.' . $host], TRUE)
      && (!$request->isSecure() || $request->getHost() !== $host)
      && $request->isMethodSafe()) {
      $event->setResponse(new RedirectResponse(SeoPolicy::baseUrl() . $request->getRequestUri(), 301));
    }
  }

  public function onResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest()) { return; }
    $request = $event->getRequest();
    $response = $event->getResponse();
    $node = $request->attributes->get('node');
    $canonical = $request->attributes->get('_route') === 'entity.node.canonical';
    $sitemap = str_starts_with((string) $request->attributes->get('_route'), 'simple_sitemap.');
    if (!SeoPolicy::isPublicRequest($request) || $response->getStatusCode() >= 400
      || (!$sitemap && (!$canonical || !$node instanceof NodeInterface || !SeoPolicy::isIndexableNode($node)))) {
      $response->headers->set('X-Robots-Tag', 'noindex, follow');
    }
  }

  public static function getSubscribedEvents(): array {
    return [KernelEvents::REQUEST => ['onRequest', 310], KernelEvents::RESPONSE => ['onResponse', -1000]];
  }

}
