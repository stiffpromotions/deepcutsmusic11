<?php

/**
 * @file
 * Build editable videos: ddev drush php:script scripts/build-videos-page.php
 * Existing page sections and Media edits are preserved on subsequent runs.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;

$path = \Drupal::service('path_alias.manager')->getPathByAlias('/videos');
if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($page = Node::load($match[1]))) {
  throw new \RuntimeException('Create the Videos page first.');
}
if (!ParagraphsType::load('video')) {
  ParagraphsType::create(['id' => 'video', 'label' => 'Video', 'description' => 'A titled video from the Media Library. Add and reorder as many videos as needed.'])->save();
}
$exports = ['paragraphs.paragraphs_type.video'];
foreach (['field_heading', 'field_background', 'field_width'] as $name) {
  if (!FieldConfig::loadByName('paragraph', 'video', $name)) {
    $source = FieldConfig::loadByName('paragraph', 'text', $name);
    $values = $source->toArray();
    unset($values['uuid'], $values['id']);
    $values['bundle'] = 'video';
    FieldConfig::create($values)->save();
  }
  $exports[] = 'field.field.paragraph.video.' . $name;
}
if (!FieldStorageConfig::loadByName('paragraph', 'field_video')) {
  FieldStorageConfig::create(['entity_type' => 'paragraph', 'field_name' => 'field_video', 'type' => 'entity_reference', 'cardinality' => 1, 'settings' => ['target_type' => 'media']])->save();
}
if (!FieldConfig::loadByName('paragraph', 'video', 'field_video')) {
  FieldConfig::create(['entity_type' => 'paragraph', 'bundle' => 'video', 'field_name' => 'field_video', 'label' => 'Video', 'description' => 'Select a video or add Remote video with a YouTube URL. Edit the Media item to replace its URL later.', 'required' => TRUE, 'settings' => ['handler' => 'default:media', 'handler_settings' => ['target_bundles' => ['remote_video' => 'remote_video', 'video' => 'video']]]])->save();
}
$form = EntityFormDisplay::load('paragraph.video.default') ?: EntityFormDisplay::create(['targetEntityType' => 'paragraph', 'bundle' => 'video', 'mode' => 'default', 'status' => TRUE]);
$form->setComponent('field_heading', ['type' => 'string_textfield', 'weight' => 0]);
$form->setComponent('field_video', ['type' => 'media_library_widget', 'weight' => 1]);
$form->setComponent('field_background', ['type' => 'options_select', 'weight' => 2]);
$form->setComponent('field_width', ['type' => 'options_select', 'weight' => 3]);
$form->removeComponent('created')->removeComponent('status')->save();
$display = EntityViewDisplay::load('paragraph.video.default') ?: EntityViewDisplay::create(['targetEntityType' => 'paragraph', 'bundle' => 'video', 'mode' => 'default', 'status' => TRUE]);
$display->setComponent('field_video', ['type' => 'entity_reference_entity_view', 'label' => 'hidden', 'settings' => ['view_mode' => 'default', 'link' => FALSE]]);
foreach (['field_heading', 'field_background', 'field_width'] as $name) { $display->removeComponent($name); }
$display->save();
$sections = FieldConfig::loadByName('node', 'page', 'field_sections');
$settings = $sections->getSetting('handler_settings');
$settings['target_bundles']['video'] = 'video';
$sections->setSetting('handler_settings', $settings)->save();

if ($page->get('field_sections')->isEmpty()) {
  $references = [];
  foreach ([['Electric', 'https://www.youtube.com/watch?v=uzZFKqJkdME', 'white'], ['Acoustic', 'https://www.youtube.com/watch?v=khAwa7z6C_Q', 'white']] as [$title, $url, $background]) {
    $existing = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle' => 'remote_video', 'name' => 'Videos: ' . $title]);
    $media = $existing ? reset($existing) : Media::create(['bundle' => 'remote_video', 'name' => 'Videos: ' . $title, 'status' => TRUE, 'uid' => 1, 'field_media_oembed_video' => $url]);
    if ($media->isNew()) { $media->save(); }
    $paragraph = Paragraph::create(['type' => 'video', 'field_heading' => $title, 'field_video' => $media->id(), 'field_background' => $background, 'field_width' => 'standard']);
    $paragraph->save();
    $references[] = ['target_id' => $paragraph->id(), 'target_revision_id' => $paragraph->getRevisionId()];
  }
  $page->set('field_sections', $references);
  // Reuse the current shared hero placeholder until final assets arrive.
  if ($page->get('field_hero_image')->isEmpty()) {
    $about_path = \Drupal::service('path_alias.manager')->getPathByAlias('/about');
    if (preg_match('@^/node/(\d+)$@', $about_path, $about_match) && ($about = Node::load($about_match[1]))) {
      $page->set('field_hero_image', $about->get('field_hero_image')->getValue());
    }
  }
  $page->setNewRevision(TRUE);
  $page->setRevisionLogMessage('Build Videos with editable Electric and Acoustic placeholder video sections.');
  $page->save();
}
array_push($exports, 'field.storage.paragraph.field_video', 'field.field.paragraph.video.field_video', 'core.entity_form_display.paragraph.video.default', 'core.entity_view_display.paragraph.video.default', 'field.field.node.page.field_sections');
foreach ($exports as $name) {
  file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
}
echo "Videos configured; existing content preserved.\n";
require __DIR__ . '/update-videos-figma.php';
