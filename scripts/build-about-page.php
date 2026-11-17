<?php

/**
 * @file
 * Build About content: ddev drush php:script scripts/build-about-page.php
 * Preserves existing Media, members and page sections on repeated runs.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$exports = [];
foreach (['dc_about_intro' => [480, 450, 'About introduction'], 'dc_member_portrait' => [420, 260, 'Band member portrait']] as $id => [$width, $height, $label]) {
  if (!ImageStyle::load($id)) {
    $style = ImageStyle::create(['name' => $id, 'label' => "$label ($width × $height)"]);
    $style->addImageEffect(['id' => 'focal_point_scale_and_crop', 'weight' => 0, 'data' => ['width' => $width, 'height' => $height, 'crop_type' => 'focal_point']]);
    $style->save();
  }
  if (!EntityViewMode::load('media.' . $id)) {
    EntityViewMode::create(['id' => 'media.' . $id, 'label' => $label, 'targetEntityType' => 'media', 'status' => TRUE])->save();
  }
  $display = EntityViewDisplay::load('media.image.' . $id);
  if (!$display) {
    $display = EntityViewDisplay::create(['targetEntityType' => 'media', 'bundle' => 'image', 'mode' => $id, 'status' => TRUE]);
    $display->setComponent('field_media_image', ['type' => 'image', 'label' => 'hidden', 'settings' => ['image_style' => $id, 'image_link' => '', 'image_loading' => ['attribute' => 'lazy']]]);
    foreach (['name', 'created', 'uid', 'langcode', 'thumbnail'] as $field) { $display->removeComponent($field); }
    $display->save();
  }
  array_push($exports, 'image.style.' . $id, 'core.entity_view_mode.media.' . $id, 'core.entity_view_display.media.image.' . $id);
}

// Image-and-text sections can opt into the fixed intro crop; default stays natural.
if (!FieldStorageConfig::loadByName('paragraph', 'field_image_preset')) {
  FieldStorageConfig::create(['entity_type' => 'paragraph', 'field_name' => 'field_image_preset', 'type' => 'list_string', 'cardinality' => 1, 'settings' => ['allowed_values' => ['natural' => 'Natural image ratio', 'about_intro' => 'About introduction (480 × 450)']]])->save();
}
if (!FieldConfig::loadByName('paragraph', 'image_text', 'field_image_preset')) {
  FieldConfig::create(['entity_type' => 'paragraph', 'bundle' => 'image_text', 'field_name' => 'field_image_preset', 'label' => 'Image preset', 'required' => TRUE, 'default_value' => [['value' => 'natural']]])->save();
}
if (!FieldConfig::loadByName('paragraph', 'image_text', 'field_bordered')) {
  FieldConfig::create(['entity_type' => 'paragraph', 'bundle' => 'image_text', 'field_name' => 'field_bordered', 'label' => 'Bordered introduction', 'settings' => ['on_label' => 'Bordered', 'off_label' => 'Plain'], 'default_value' => [['value' => 0]]])->save();
}
$form = EntityFormDisplay::load('paragraph.image_text.default');
$form->setComponent('field_image_preset', ['type' => 'options_select', 'weight' => 8]);
$form->setComponent('field_bordered', ['type' => 'boolean_checkbox', 'weight' => 9])->save();
$display = EntityViewDisplay::load('paragraph.image_text.default');
$display->removeComponent('field_image_preset')->removeComponent('field_bordered')->save();
$display = EntityViewDisplay::load('node.band_member.dc_card');
$display->setComponent('body', ['type' => 'text_default', 'label' => 'hidden', 'weight' => 101]);
$display->setComponent('field_image', ['type' => 'entity_reference_entity_view', 'label' => 'hidden', 'settings' => ['view_mode' => 'dc_member_portrait', 'link' => FALSE], 'weight' => 102])->save();

$directory = 'public://about';
\Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
$import = static function (string $filename, string $name, string $alt) use ($directory): Media {
  $existing = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle' => 'image', 'name' => $name]);
  if ($existing) { return reset($existing); }
  $uri = $directory . '/' . str_replace(' ', '-', strtolower($filename));
  $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
  $file = $files ? reset($files) : \Drupal::service('file.repository')->writeData(file_get_contents(DRUPAL_ROOT . '/_resources/Images/About/' . $filename), $uri, FileExists::Error);
  $file->setPermanent(); $file->save();
  $size = getimagesize(DRUPAL_ROOT . '/_resources/Images/About/' . $filename);
  $media = Media::create(['bundle' => 'image', 'name' => $name, 'status' => TRUE, 'uid' => 1, 'field_media_image' => ['target_id' => $file->id(), 'alt' => $alt, 'width' => $size[0], 'height' => $size[1]]]);
  $media->save();
  return $media;
};
$intro = $import('About Intro.jpg', 'About: Deep Cuts together', 'The four members of Deep Cuts together on stage');
$data = json_decode(file_get_contents(__DIR__ . '/data/about.json'), TRUE, 512, JSON_THROW_ON_ERROR);
foreach ($data['members'] as $order => $member) {
  $portrait = $import($member['name'] . '.jpg', 'About: ' . $member['name'], $member['name'] . ' of Deep Cuts');
  $existing = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'band_member', 'title' => $member['name']]);
  if (!$existing) {
    Node::create(['type' => 'band_member', 'title' => $member['name'], 'status' => TRUE, 'uid' => 1, 'promote' => FALSE, 'field_role' => $member['role'], 'field_display_order' => $order, 'field_image' => $portrait->id(), 'body' => ['value' => '<p>' . htmlspecialchars($member['bio'], ENT_QUOTES, 'UTF-8') . '</p>', 'format' => 'basic_html']])->save();
  }
}
$path = \Drupal::service('path_alias.manager')->getPathByAlias('/about');
if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($page = Node::load($match[1]))) { throw new \RuntimeException('Create the About page first.'); }
if ($page->get('field_sections')->isEmpty()) {
  $intro_html = implode('', array_map(static fn ($text) => '<p>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>', $data['intro']));
  $sections = [];
  foreach ([
    ['type' => 'image_text', 'field_heading' => 'What we do', 'field_text' => ['value' => $intro_html, 'format' => 'basic_html'], 'field_section_image' => $intro->id(), 'field_image_position' => 'right', 'field_image_preset' => 'about_intro', 'field_bordered' => TRUE, 'field_link' => ['uri' => 'internal:/packages', 'title' => 'See our packages']],
    ['type' => 'page_breaker', 'field_heading' => 'Who we are', 'field_background' => 'purple-50', 'field_text' => ['value' => '<p>Live music hits a new note when you know the people behind the instruments. Read on to hear a little bit about us and our professional experience so that you can rest assured knowing that your event is in the most capable of hands.</p>', 'format' => 'basic_html']],
    ['type' => 'content_listing', 'field_listing' => 'band_member'],
  ] as $values) {
    $paragraph = Paragraph::create($values); $paragraph->save();
    $sections[] = ['target_id' => $paragraph->id(), 'target_revision_id' => $paragraph->getRevisionId()];
  }
  $page->set('field_sections', $sections);
  if ($page->get('field_hero_image')->isEmpty()) {
    $hero_path = \Drupal::service('path_alias.manager')->getPathByAlias('/repertoire');
    if (preg_match('@^/node/(\d+)$@', $hero_path, $hero_match) && ($hero = Node::load($hero_match[1]))) { $page->set('field_hero_image', $hero->get('field_hero_image')->getValue()); }
  }
  $page->setNewRevision(TRUE); $page->setRevisionLogMessage('Build About from Figma with editable Band members and focal-point image presets.'); $page->save();
}
array_push($exports, 'field.storage.paragraph.field_image_preset', 'field.field.paragraph.image_text.field_image_preset', 'field.field.paragraph.image_text.field_bordered', 'core.entity_form_display.paragraph.image_text.default', 'core.entity_view_display.paragraph.image_text.default', 'core.entity_view_display.node.band_member.dc_card');
foreach ($exports as $name) {
  file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
}
echo "About configured: five Media images, four Band members, three page sections; existing content preserved.\n";
