<?php

/**
 * @file
 * Verify without enabling maintenance: ddev drush php:script scripts/verify-editor-maintenance.php
 */

use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Site\MaintenanceMode;
use Drupal\Core\State\State;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$check = static function (bool $valid, string $message): void {
  if (!$valid) { throw new \RuntimeException($message); }
};
$state_before = \Drupal::state()->get('system.maintenance_mode');
$accounts = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => 'Iain', 'mail' => 'iain@stiffpromotions.co.uk']);
$check(count($accounts) === 1, 'Expected exactly one Iain account.');
$account = reset($accounts);
$check($account->isActive() && $account->hasRole('editor') && !$account->hasRole('administrator'), 'Incorrect account state or roles.');
foreach (['administer site configuration', 'administer permissions', 'administer users', 'administer modules', 'administer themes', 'administer content types', 'administer fields', 'administer views', 'administer taxonomy', 'administer media types', 'administer paragraphs types', 'administer meta tags', 'administer webform', 'administer redirects', 'administer menu', 'bypass node access'] as $permission) {
  $check(!$account->hasPermission($permission), 'Editor has forbidden permission: ' . $permission);
}
$access = \Drupal::service('access_manager');
foreach (['system.site_information_settings', 'system.site_maintenance_mode', 'user.admin_permissions', 'entity.user.collection', 'entity.node_type.collection', 'system.modules_list', 'system.themes_page', 'metatag.settings'] as $route) {
  $check(!$access->checkNamedRoute($route, [], $account), 'Editor can access configuration route: ' . $route);
}
$check($access->checkNamedRoute('system.admin_content', [], $account), 'Editor cannot reach Content.');
foreach (\Drupal\node\Entity\NodeType::loadMultiple() as $bundle => $type) {
  $check(\Drupal::entityTypeManager()->getAccessControlHandler('node')->createAccess($bundle, $account), 'Cannot create ' . $bundle);
  $node = Node::create(['type' => $bundle, 'title' => 'Access check only', 'uid' => 1, 'status' => 1]);
  $check($node->access('update', $account) && $node->access('delete', $account), 'Cannot edit/delete another author’s ' . $bundle);
}
foreach (\Drupal\media\Entity\MediaType::loadMultiple() as $bundle => $type) {
  $check(\Drupal::entityTypeManager()->getAccessControlHandler('media')->createAccess($bundle, $account), 'Cannot create Media: ' . $bundle);
}
\Drupal::service('account_switcher')->switchTo($account);
try {
  $form = \Drupal::service('entity.form_builder')->getForm(Node::load(4));
  $check(isset($form['field_sections'], $form['field_metatags'], $form['field_seo']), 'Page editor controls missing.');
}
finally { \Drupal::service('account_switcher')->switchBack(); }

// This state object reports maintenance only within this test; it never writes.
$preview_state = new class extends State {
  public function __construct() {}
  public function get($key, $default = NULL) {
    return $key === 'system.maintenance_mode' ? TRUE : \Drupal::state()->get($key, $default);
  }
};
$mode = new MaintenanceMode($preview_state, \Drupal::configFactory());
$routes = \Drupal::service('router.route_provider');
$check($mode->applies(new RouteMatch('entity.node.canonical', $routes->getRouteByName('entity.node.canonical'))), 'Maintenance does not apply to content.');
foreach (['user.login', 'user.pass', 'user.reset'] as $route) {
  $check(!$mode->applies(new RouteMatch($route, $routes->getRouteByName($route))), 'Authentication route blocked during maintenance: ' . $route);
}
$check($mode->exempt($account) && !$mode->exempt(new \Drupal\Core\Session\AnonymousUserSession()), 'Maintenance preview access incorrect.');
$subscriber = new \Drupal\Core\EventSubscriber\MaintenanceModeSubscriber(
  $mode, \Drupal::configFactory(), \Drupal::translation(), \Drupal::service('url_generator'),
  \Drupal::currentUser(), \Drupal::service('bare_html_page_renderer'), \Drupal::messenger(),
  \Drupal::service('event_dispatcher'), $preview_state, static fn () => \Drupal::logger('system')
);
$request = Request::create('https://deepcutsmusic11.ddev.site/');
$event = new RequestEvent(\Drupal::service('http_kernel'), $request, HttpKernelInterface::MAIN_REQUEST);
$subscriber->onMaintenanceModeRequest($event);
$response = $event->getResponse();
$check($response && $response->getStatusCode() === 503, 'Maintenance response must be HTTP 503.');
$html = $response->getContent();
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
$check($xpath->query('//h1')->length === 1 && str_contains($html, 'Back on stage soon'), 'Themed maintenance heading missing.');
$check($xpath->query('//title')->item(0)?->textContent === 'Back on stage soon | Deep Cuts', 'Maintenance browser title incorrect.');
$check($xpath->query('//meta[@name="robots"]')->item(0)?->getAttribute('content') === 'noindex, follow', 'Maintenance noindex missing.');
$button = $xpath->query('//a[contains(@class, "dc-maintenance__login")]')->item(0);
$check($button && trim($button->textContent) === 'Admin login' && str_contains($button->getAttribute('href'), '/user/login'), 'Admin login button missing/incorrect.');
$check(str_contains($html, 'fine-tuning') && str_contains($html, 'css/style.css'), 'Maintenance message/styles missing.');
$check(\Drupal::state()->get('system.maintenance_mode') === $state_before, 'Actual maintenance state changed.');
// Opt-in temporary local visual QA artifact; remove after reviewing it.
if (getenv('DEEPCUTS_MAINTENANCE_PREVIEW') === '1') {
  file_put_contents(DRUPAL_ROOT . '/tmp/maintenance-preview.html', str_replace('<head>', '<head><base href="https://deepcutsmusic11.ddev.site/">', $html));
}
echo "Verified Editor content CRUD, Media creation, configuration denials, account identity, maintenance exemption, login/reset availability and themed 503 response. Real maintenance state unchanged.\n";
