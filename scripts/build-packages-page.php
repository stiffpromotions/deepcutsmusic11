<?php

/**
 * @file
 * Build Packages: ddev drush php:script scripts/build-packages-page.php
 */

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$exports = [];
$id = 'dc_package_style';
if (!ImageStyle::load($id)) {
  $style = ImageStyle::create(['name' => $id, 'label' => 'Package style (307 × 260)']);
  $style->addImageEffect(['id' => 'focal_point_scale_and_crop', 'weight' => 0, 'data' => ['width' => 307, 'height' => 260, 'crop_type' => 'focal_point']]);
  $style->save();
}
if (!EntityViewMode::load('media.' . $id)) {
  EntityViewMode::create(['id' => 'media.' . $id, 'label' => 'Package style', 'targetEntityType' => 'media', 'status' => TRUE])->save();
}
if (!EntityViewDisplay::load('media.image.' . $id)) {
  $display = EntityViewDisplay::create(['targetEntityType' => 'media', 'bundle' => 'image', 'mode' => $id, 'status' => TRUE]);
  $display->setComponent('field_media_image', ['type' => 'image', 'label' => 'hidden', 'settings' => ['image_style' => $id, 'image_link' => '', 'image_loading' => ['attribute' => 'lazy']]]);
  foreach (['name', 'created', 'uid', 'langcode', 'thumbnail'] as $field) { $display->removeComponent($field); }
  $display->save();
}
array_push($exports, 'image.style.' . $id, 'core.entity_view_mode.media.' . $id, 'core.entity_view_display.media.image.' . $id);
foreach (['package', 'add_on'] as $bundle) {
  $display = EntityViewDisplay::load('node.' . $bundle . '.dc_card');
  $display->setComponent('body', ['type' => 'text_default', 'label' => 'hidden', 'weight' => 101]);
  $display->setComponent('field_image', ['type' => 'entity_reference_entity_view', 'label' => 'hidden', 'settings' => ['view_mode' => $id, 'link' => FALSE], 'weight' => 102])->save();
  $exports[] = 'core.entity_view_display.node.' . $bundle . '.dc_card';
}
$directory = 'public://packages';
\Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
$data = json_decode(file_get_contents(__DIR__ . '/data/packages.json'), TRUE, 512, JSON_THROW_ON_ERROR);
$addons = [];
foreach (['add_ons' => 'add_on', 'styles' => 'package'] as $key => $bundle) {
  foreach ($data[$key] as $order => $item) {
    $media = NULL;
    if ($bundle === 'package') {
      $name = 'Packages: ' . $item['title'];
      $existing = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle' => 'image', 'name' => $name]);
      $media = $existing ? reset($existing) : NULL;
      if (!$media) {
        $source = DRUPAL_ROOT . '/_resources/Images/Packages/' . $item['title'] . '.jpg';
        $uri = $directory . '/' . strtolower($item['title']) . '.jpg';
        $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
        $file = $files ? reset($files) : \Drupal::service('file.repository')->writeData(file_get_contents($source), $uri, FileExists::Error);
        $file->setPermanent(); $file->save();
        $size = getimagesize($source);
        $media = Media::create(['bundle' => 'image', 'name' => $name, 'status' => TRUE, 'uid' => 1, 'field_media_image' => ['target_id' => $file->id(), 'alt' => 'Deep Cuts performing in their ' . strtolower($item['title']) . ' style', 'width' => $size[0], 'height' => $size[1]]]);
        $media->save();
      }
    }
    $existing = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => $bundle, 'title' => $item['title']]);
    $node = $existing ? reset($existing) : NULL;
    if (!$node) {
      $values = ['type' => $bundle, 'title' => $item['title'], 'status' => TRUE, 'uid' => 1, 'promote' => FALSE, 'body' => ['value' => $item['body'], 'format' => 'basic_html'], 'field_display_order' => $order];
      if ($media) { $values['field_image'] = $media->id(); }
      if ($bundle === 'package') { $values['field_add_ons'] = $addons; }
      $node = Node::create($values); $node->save();
    }
    if ($bundle === 'add_on') { $addons[] = ['target_id' => $node->id()]; }
  }
}
$path = \Drupal::service('path_alias.manager')->getPathByAlias('/packages');
if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($page = Node::load($match[1]))) { throw new \RuntimeException('Create the Packages page first.'); }
if ($page->get('field_sections')->isEmpty()) {
  $sections = [];
  foreach ([
    ['type' => 'text', 'field_heading' => 'Your entertainment package', 'field_bordered' => TRUE, 'field_text' => ['value' => file_get_contents(__DIR__ . '/data/packages-intro.html'), 'format' => 'full_html']],
    ['type' => 'page_breaker', 'field_heading' => 'Styles', 'field_background' => 'purple-50', 'field_text' => ['value' => '<p>Our presentation is available in three styles: Electric, Acoustic and Roaming. Not sure which is best for your event? Have a read below or <a href="/book-now">get in touch</a> for more details.</p>', 'format' => 'basic_html']],
    ['type' => 'content_listing', 'field_listing' => 'package'],
    ['type' => 'page_breaker', 'field_heading' => 'Your Event, Your Way: Upgrades & Add-ons', 'field_background' => 'purple-50', 'field_text' => ['value' => '<p>Are you planning a particularly unique event? We’re experienced in delivering alternative professional events and thrive on delivering tailored experiences. Let us know what you’re planning and we’ll build you a one-of-a-kind package.</p>', 'format' => 'basic_html']],
    ['type' => 'content_listing', 'field_listing' => 'add_on'],
  ] as $values) {
    $paragraph = Paragraph::create($values); $paragraph->save();
    $sections[] = ['target_id' => $paragraph->id(), 'target_revision_id' => $paragraph->getRevisionId()];
  }
  $page->set('field_sections', $sections);
  if ($page->get('field_hero_image')->isEmpty()) {
    $hero_path = \Drupal::service('path_alias.manager')->getPathByAlias('/repertoire');
    if (preg_match('@^/node/(\d+)$@', $hero_path, $hero_match) && ($hero = Node::load($hero_match[1]))) { $page->set('field_hero_image', $hero->get('field_hero_image')->getValue()); }
  }
  $page->setNewRevision(TRUE); $page->setRevisionLogMessage('Build Packages from Figma with three styles and eleven editable add-ons.'); $page->save();
}
foreach ($exports as $name) {
  file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
}
echo "Packages configured: three styles, eleven add-ons, three Media images and five sections. Existing content preserved.\n";
