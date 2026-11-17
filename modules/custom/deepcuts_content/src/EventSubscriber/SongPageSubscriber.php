<?php

declare(strict_types=1);

namespace Drupal\deepcuts_content\EventSubscriber;

use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Keeps supporting content reusable without standalone public pages.
 */
final class SongPageSubscriber implements EventSubscriberInterface {

  /**
   * Responds after routing and entity parameter conversion.
   */
  public function onRequest(RequestEvent $event): void {
    $attributes = $event->getRequest()->attributes;
    if ($attributes->get('_route') !== 'entity.node.canonical') {
      return;
    }
    $node = $attributes->get('node');
    if ($node instanceof NodeInterface && in_array($node->bundle(), ['song', 'review', 'faq', 'band_member', 'package', 'add_on', 'logo'], TRUE)) {
      // Let Drupal render its configured 404 page, retaining the requested URL.
      throw new NotFoundHttpException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [KernelEvents::REQUEST => ['onRequest', 0]];
  }

}
