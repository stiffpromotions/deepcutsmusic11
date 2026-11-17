<?php

/**
 * @file
 * Convert an unused Article image field to image-only Media.
 *
 * Run: ddev drush php:script scripts/article-image-media.php
 * Refuses to convert if any Article (including unpublished) or other bundle
 * uses the original field. Export final configuration for normal deployment.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Field\FieldPurger;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\media\Entity\MediaType;

$media_type = MediaType::load('image');
if (!$media_type || $media_type->getSource()->getPluginId() !== 'image'
  || !\Drupal::moduleHandler()->moduleExists('media_library')) {
  throw new RuntimeException('Image Media and Media Library must exist first.');
}
$storage = FieldStorageConfig::loadByName('node', 'field_image');
$field = FieldConfig::loadByName('node', 'article', 'field_image');
$form = EntityFormDisplay::load('node.article.default');
if (!$form || !$field || !$storage) {
  throw new RuntimeException('Expected Article image configuration is missing.');
}
$field_values = $field->toArray();
$widget = $form->getComponent('field_image');
$displays = EntityViewDisplay::loadMultiple();
$components = [];
foreach ($displays as $id => $display) {
  if ($display->getTargetEntityTypeId() === 'node' && $display->getTargetBundle() === 'article') {
    $components[$id] = $display->getComponent('field_image');
  }
}

if ($storage->getType() === 'image') {
  $articles = \Drupal::entityQuery('node')->condition('type', 'article')->accessCheck(FALSE)->count()->execute();
  if ($articles || array_values($storage->getBundles()) !== ['article'] || $storage->hasData()) {
    throw new RuntimeException('Existing content or shared storage requires a migration. No changes made.');
  }
  $storage_id = $storage->getUniqueStorageIdentifier();
  $field->delete();
  \Drupal::service(FieldPurger::class)->purgeBatch(100, $storage_id);
  $storage = FieldStorageConfig::create([
    'entity_type' => 'node', 'field_name' => 'field_image',
    'type' => 'entity_reference', 'settings' => ['target_type' => 'media'],
    'cardinality' => 1, 'translatable' => TRUE,
  ]);
  $storage->save();
  $field = FieldConfig::create([
    'entity_type' => 'node', 'bundle' => 'article', 'field_name' => 'field_image',
    'label' => $field_values['label'], 'description' => $field_values['description'],
    'required' => $field_values['required'], 'translatable' => $field_values['translatable'],
  ]);
}
elseif ($storage->getType() !== 'entity_reference' || $storage->getSetting('target_type') !== 'media') {
  throw new RuntimeException('Unexpected field storage type.');
}
$field->setSetting('handler', 'default:media');
$field->setSetting('handler_settings', [
  'target_bundles' => ['image' => 'image'],
  'sort' => ['field' => '_none', 'direction' => 'ASC'],
  'auto_create' => FALSE, 'auto_create_bundle' => '',
]);
$field->save();

// Reload displays after field deletion and recreate only its former components.
$form = EntityFormDisplay::load('node.article.default');
$form->setComponent('field_image', [
  'type' => 'media_library_widget', 'weight' => $widget['weight'] ?? 1,
  'region' => 'content', 'settings' => ['media_types' => ['image']],
])->save();
$source_field = $media_type->getSource()->getConfiguration()['source_field'];
foreach (['article_image' => ['wide', 'eager'], 'article_teaser' => ['medium', 'lazy']] as $mode => [$style, $loading]) {
  if (!EntityViewMode::load('media.' . $mode)) {
    EntityViewMode::create([
      'id' => 'media.' . $mode, 'label' => ucwords(str_replace('_', ' ', $mode)),
      'targetEntityType' => 'media', 'status' => TRUE,
    ])->save();
  }
  $display = EntityViewDisplay::load('media.image.' . $mode) ?? EntityViewDisplay::create([
    'targetEntityType' => 'media', 'bundle' => 'image', 'mode' => $mode, 'status' => TRUE,
  ]);
  $display->setComponent($source_field, [
    'type' => 'image', 'label' => 'hidden',
    'settings' => ['image_style' => $style, 'image_link' => '', 'image_loading' => ['attribute' => $loading]],
  ])->save();
}
foreach ($components as $id => $component) {
  $display = EntityViewDisplay::load($id);
  if ($component !== NULL) {
    $mode = $display->getMode() === 'teaser' ? 'article_teaser' : 'article_image';
    $display->setComponent('field_image', [
      'type' => 'entity_reference_entity_view', 'label' => 'hidden',
      'weight' => $component['weight'], 'region' => 'content',
      'settings' => ['view_mode' => $mode, 'link' => FALSE],
    ])->save();
  }
}
echo "Article field_image now references Image Media only.\n";
