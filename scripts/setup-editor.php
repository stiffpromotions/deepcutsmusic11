<?php

/**
 * @file
 * Create Editor/Iain: ddev drush php:script scripts/setup-editor.php
 * Does not send email, expose a password or change maintenance mode.
 */

use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

$permissions = [
  'access content', 'access content overview', 'access administration pages',
  'access toolbar', 'access contextual links', 'view the administration theme',
  'access site in maintenance mode', 'view own unpublished content',
  'view all revisions', 'revert all revisions', 'view unpublished paragraphs',
  'view media', 'access media overview', 'view own unpublished media',
  'access files overview', 'delete own files', 'create url aliases',
  'use text format basic_html', 'use text format restricted_html', 'use yoast seo',
  'access taxonomy overview',
];
foreach (\Drupal\node\Entity\NodeType::loadMultiple() as $bundle => $type) {
  array_push($permissions, "create $bundle content", "edit any $bundle content", "delete any $bundle content");
}
foreach (\Drupal\media\Entity\MediaType::loadMultiple() as $bundle => $type) {
  array_push($permissions, "create $bundle media", "edit any $bundle media", "delete any $bundle media");
}
foreach (\Drupal\taxonomy\Entity\Vocabulary::loadMultiple() as $bundle => $vocabulary) {
  array_push($permissions, "create terms in $bundle", "edit terms in $bundle", "delete terms in $bundle");
}
$available = \Drupal::service('user.permissions')->getPermissions();
foreach ($permissions as $permission) {
  if (!isset($available[$permission])) { throw new \RuntimeException('Unknown permission: ' . $permission); }
}
$users = \Drupal::entityTypeManager()->getStorage('user');
$by_name = $users->loadByProperties(['name' => 'Iain']);
$by_mail = $users->loadByProperties(['mail' => 'iain@stiffpromotions.co.uk']);
$account = $by_name ? reset($by_name) : ($by_mail ? reset($by_mail) : NULL);
if ($account && ($account->getAccountName() !== 'Iain' || $account->getEmail() !== 'iain@stiffpromotions.co.uk')) {
  throw new \RuntimeException('Conflicting username/email; existing account left unchanged.');
}
if ($account && array_diff($account->getRoles(), ['authenticated', 'editor'])) {
  throw new \RuntimeException('Existing account has other roles; left unchanged for review.');
}
$role = Role::load('editor') ?: Role::create(['id' => 'editor', 'label' => 'Editor', 'weight' => 3]);
$role->setIsAdmin(FALSE)->set('permissions', array_values(array_unique($permissions)))->save();
if (!$account) {
  $account = User::create(['name' => 'Iain', 'mail' => 'iain@stiffpromotions.co.uk', 'status' => 1, 'roles' => ['editor']]);
  $account->setPassword(bin2hex(random_bytes(32)));
  $account->save();
  echo "Created Iain with Editor role. Password reset required; no email sent.\n";
}
elseif (!$account->hasRole('editor')) {
  $account->addRole('editor'); $account->save();
}
// Keep the general maintenance message editable in Drupal's Maintenance mode UI.
\Drupal::configFactory()->getEditable('system.maintenance')
  ->set('message', 'We’re fine-tuning the Deep Cuts website. Please check back soon.')->save();
foreach (['user.role.editor', 'system.maintenance'] as $name) {
  file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
}
echo "Editor permissions and maintenance message configured. Maintenance state unchanged.\n";
