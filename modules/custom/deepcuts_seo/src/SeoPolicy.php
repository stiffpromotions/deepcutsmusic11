<?php

declare(strict_types=1);

namespace Drupal\deepcuts_seo;

use Drupal\Core\Site\Settings;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Shared indexing and URL rules for metadata, responses and sitemaps.
 */
final class SeoPolicy {

  public static function baseUrl(): string {
    return rtrim(\Drupal::config('deepcuts_seo.settings')->get('public_base_url'), '/');
  }

  public static function publicUrl(string $path): string {
    return self::baseUrl() . '/' . ltrim($path, '/');
  }

  public static function canonical(NodeInterface $node): string {
    if ('/node/' . $node->id() === \Drupal::config('system.site')->get('page.front')) {
      return self::publicUrl('/');
    }
    return self::publicUrl($node->toUrl()->setAbsolute(FALSE)->toString());
  }

  public static function isPublicRequest(Request $request): bool {
    return Settings::get('deepcuts_seo_indexing_enabled', FALSE)
      && $request->isSecure()
      && $request->getHost() === parse_url(self::baseUrl(), PHP_URL_HOST);
  }

  public static function isIndexableNode(NodeInterface $node): bool {
    if (!$node->isPublished() || !in_array($node->bundle(), ['page', 'article'], TRUE)) {
      return FALSE;
    }
    $path = '/node/' . $node->id();
    $aliases = \Drupal::service('path_alias.manager');
    foreach (['/thank-you', '/error'] as $alias) {
      if ($aliases->getPathByAlias($alias) === $path) { return FALSE; }
    }
    if ($node->hasField('field_metatags') && !$node->get('field_metatags')->isEmpty()) {
      $tags = metatag_data_decode($node->get('field_metatags')->value);
      if (in_array('noindex', array_map('trim', explode(',', $tags['robots'] ?? '')), TRUE)) { return FALSE; }
    }
    return TRUE;
  }

  public static function inSitemap(NodeInterface $node): bool {
    $path = '/node/' . $node->id();
    foreach (['/privacy-policy', '/cookie-policy'] as $alias) {
      if (\Drupal::service('path_alias.manager')->getPathByAlias($alias) === $path) { return FALSE; }
    }
    return self::isIndexableNode($node);
  }

}
