<?php

/**
 * @file
 * Creates the Deep Cuts content model through Drupal entity APIs.
 *
 * Run with: ddev drush php:script scripts/build-content-model.php
 * Existing fields/types are preserved; incompatible storage stops execution.
 * Export resulting configuration for normal deployment.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

foreach (['node', 'text', 'taxonomy', 'media', 'media_library', 'link', 'datetime'] as $module) {
  if (!\Drupal::moduleHandler()->moduleExists($module)) {
    throw new RuntimeException("Required module is not enabled: $module");
  }
}

$types = [
  'article' => ['Article', 'News, announcements, and gig reports.'],
  'page' => ['Basic page', 'Site pages, introductions, policies, and landing pages.'],
  'review' => ['Review', 'An individual client review for Testimonials and homepage selections.'],
  'faq' => ['FAQ', 'A question and answer, optionally grouped by topic.'],
  'band_member' => ['Band member', 'A band member profile for About and reusable listings.'],
  'package' => ['Package', 'An entertainment package and its inclusions.'],
  'add_on' => ['Add-on', 'An optional service or enhancement to a package.'],
  'song' => ['Song', 'A repertoire entry with artist and optional category.'],
];
$vocabularies = [
  'event_type' => ['Event types', 'Event types used to group reviews.'],
  'faq_category' => ['FAQ categories', 'Topics used to group frequently asked questions.'],
  'repertoire_category' => ['Repertoire categories', 'Sections used to group songs in the repertoire.'],
];
foreach ($vocabularies as $id => [$label, $description]) {
  if (!Vocabulary::load($id)) {
    Vocabulary::create(['vid' => $id, 'name' => $label, 'description' => $description])->save();
  }
}
foreach ($types as $id => [$label, $description]) {
  if (!NodeType::load($id)) {
    NodeType::create([
      'type' => $id, 'name' => $label, 'description' => $description,
      'new_revision' => TRUE, 'preview_mode' => 1, 'display_submitted' => FALSE,
    ])->save();
  }
}

// Definitions: bundle, machine name, label, type, required, field settings,
// storage settings, cardinality, help, optional default value.
$definitions = [];
$add = static function ($bundle, $name, $label, $type, $required = FALSE, $settings = [], $storage = [], $cardinality = 1, $help = '', $default = NULL) use (&$definitions) {
  $definitions[] = compact('bundle', 'name', 'label', 'type', 'required', 'settings', 'storage', 'cardinality', 'help', 'default');
};
$media = static function ($bundles) {
  return ['handler' => 'default:media', 'handler_settings' => [
    'target_bundles' => array_combine($bundles, $bundles), 'auto_create' => FALSE,
  ]];
};
$taxonomy = static function ($vocabulary) {
  return ['handler' => 'default:taxonomy_term', 'handler_settings' => [
    'target_bundles' => [$vocabulary => $vocabulary], 'auto_create' => TRUE,
  ]];
};
foreach (['page', 'band_member', 'package', 'add_on'] as $bundle) {
  $label = match ($bundle) { 'band_member' => 'Biography', 'package', 'add_on' => 'Description', default => 'Body' };
  $add($bundle, 'body', $label, 'text_with_summary', $bundle !== 'page');
}
// Article retains its existing image-only Media field and body.
foreach (['band_member', 'package', 'add_on'] as $bundle) {
  $add($bundle, 'field_image', $bundle === 'band_member' ? 'Portrait' : 'Image', 'entity_reference', FALSE, $media(['image']), ['target_type' => 'media']);
}
$add('page', 'field_hero_image', 'Hero image', 'entity_reference', FALSE, $media(['image']), ['target_type' => 'media']);
$add('page', 'field_hero_video', 'Hero video', 'entity_reference', FALSE, $media(['video', 'remote_video']), ['target_type' => 'media'], 1, 'Optional homepage promo video. Playback behaviour is implemented by the page template.');
$add('page', 'field_subtitle', 'Subtitle', 'string', FALSE, [], ['max_length' => 255]);
$add('review', 'field_quote', 'Review quote', 'text_long', TRUE);
$add('review', 'field_reviewer', 'Reviewer display name', 'string', TRUE, [], ['max_length' => 255], 1, 'The name or attribution approved for public display.');
$add('review', 'field_event_type', 'Event type', 'entity_reference', FALSE, $taxonomy('event_type'), ['target_type' => 'taxonomy_term']);
$add('review', 'field_event_date', 'Event date', 'datetime', FALSE, [], ['datetime_type' => 'date']);
$add('review', 'field_source_link', 'Review source', 'link', FALSE, ['title' => 1, 'link_type' => 16]);
$add('review', 'field_featured', 'Featured on homepage', 'boolean', FALSE, ['on_label' => 'Featured', 'off_label' => 'Not featured'], [], 1, '', 0);
$add('faq', 'field_answer', 'Answer', 'text_long', TRUE);
$add('faq', 'field_faq_category', 'Category', 'entity_reference', FALSE, $taxonomy('faq_category'), ['target_type' => 'taxonomy_term']);
$add('band_member', 'field_role', 'Role / instrument', 'string', TRUE, [], ['max_length' => 255]);
$add('package', 'field_inclusions', 'Inclusions', 'text_long', TRUE, [], [], 1, 'Describe what is included in this package; lists are supported.');
foreach (['package', 'add_on'] as $bundle) {
  $add($bundle, 'field_price_wording', 'Price wording', 'string', FALSE, [], ['max_length' => 255], 1, 'Optional display wording, such as a starting price or contact-for-quote message.');
}
$add('package', 'field_add_ons', 'Available add-ons', 'entity_reference', FALSE, [
  'handler' => 'default:node', 'handler_settings' => ['target_bundles' => ['add_on' => 'add_on'], 'auto_create' => FALSE],
], ['target_type' => 'node'], -1, 'Optional add-ons offered with this package.');
$add('song', 'field_artist', 'Original artist', 'string', TRUE, [], ['max_length' => 255]);
$add('song', 'field_repertoire_category', 'Repertoire category', 'entity_reference', FALSE, $taxonomy('repertoire_category'), ['target_type' => 'taxonomy_term'], -1);
foreach (['review', 'faq', 'band_member', 'package', 'add_on', 'song'] as $bundle) {
  $add($bundle, 'field_display_order', 'Display order', 'integer', FALSE, [], [], 1, 'Lower numbers appear first when a listing uses this field.', 0);
}

// Preflight shared storage before creating fields.
foreach ($definitions as $d) {
  $storage = FieldStorageConfig::loadByName('node', $d['name']);
  if ($storage && ($storage->getType() !== $d['type'] || $storage->getCardinality() !== $d['cardinality'])) {
    throw new RuntimeException('Incompatible existing storage: ' . $d['name']);
  }
  foreach ($d['storage'] as $key => $value) {
    if ($storage && $storage->getSetting($key) !== $value) {
      throw new RuntimeException('Incompatible setting on ' . $d['name'] . ': ' . $key);
    }
  }
}

foreach ($definitions as $weight => $d) {
  $storage = FieldStorageConfig::loadByName('node', $d['name']);
  if (!$storage) {
    $storage = FieldStorageConfig::create([
      'entity_type' => 'node', 'field_name' => $d['name'], 'type' => $d['type'],
      'settings' => $d['storage'], 'cardinality' => $d['cardinality'], 'translatable' => TRUE,
    ]);
    $storage->save();
  }
  if (FieldConfig::loadByName('node', $d['bundle'], $d['name'])) {
    continue;
  }
  $field = FieldConfig::create([
    'entity_type' => 'node', 'bundle' => $d['bundle'], 'field_name' => $d['name'],
    'label' => $d['label'], 'required' => $d['required'], 'description' => $d['help'],
    'settings' => $d['settings'], 'translatable' => TRUE,
  ]);
  if ($d['default'] !== NULL) {
    $field->setDefaultValue($d['default']);
  }
  $field->save();
  $widget = match ($d['type']) {
    'text_with_summary' => 'text_textarea_with_summary',
    'text_long' => 'text_textarea',
    'boolean' => 'boolean_checkbox',
    'integer' => 'number',
    'datetime' => 'datetime_default',
    'link' => 'link_default',
    'entity_reference' => $d['storage']['target_type'] === 'media' ? 'media_library_widget' : 'entity_reference_autocomplete',
    default => 'string_textfield',
  };
  $form = EntityFormDisplay::load('node.' . $d['bundle'] . '.default') ?? EntityFormDisplay::create([
    'targetEntityType' => 'node', 'bundle' => $d['bundle'], 'mode' => 'default', 'status' => TRUE,
  ]);
  if (!$form->getComponent('title')) {
    $form->setComponent('title', ['type' => 'string_textfield', 'weight' => -10]);
    $form->setComponent('status', ['type' => 'boolean_checkbox', 'weight' => 100]);
    $form->setComponent('path', ['type' => 'path', 'weight' => 90]);
  }
  $widget_settings = $widget === 'media_library_widget' ? ['media_types' => array_values($d['settings']['handler_settings']['target_bundles'])] : [];
  $form->setComponent($d['name'], ['type' => $widget, 'weight' => $weight, 'settings' => $widget_settings])->save();
  $view = EntityViewDisplay::load('node.' . $d['bundle'] . '.default') ?? EntityViewDisplay::create([
    'targetEntityType' => 'node', 'bundle' => $d['bundle'], 'mode' => 'default', 'status' => TRUE,
  ]);
  if (in_array($d['name'], ['field_display_order', 'field_featured', 'field_hero_image', 'field_hero_video'], TRUE)) {
    $view->removeComponent($d['name']);
  }
  else {
    $formatter = match ($d['type']) {
      'text_with_summary', 'text_long' => 'text_default',
      'datetime' => 'datetime_default',
      'link' => 'link',
      'entity_reference' => $d['storage']['target_type'] === 'media' ? 'entity_reference_entity_view' : 'entity_reference_label',
      default => 'string',
    };
    $settings = $formatter === 'entity_reference_entity_view' ? ['view_mode' => 'default', 'link' => FALSE] : [];
    if ($formatter === 'entity_reference_label') {
      $settings = ['link' => FALSE];
    }
    $view->setComponent($d['name'], ['type' => $formatter, 'label' => 'hidden', 'weight' => $weight, 'settings' => $settings]);
  }
  $view->save();
  echo 'Created ' . $d['bundle'] . '.' . $d['name'] . PHP_EOL;
}

// Public-facing title labels and defaults for newly introduced content types.
$title_labels = ['review' => 'Review title (editorial label)', 'faq' => 'Question', 'band_member' => 'Name', 'package' => 'Package name', 'add_on' => 'Add-on name', 'song' => 'Song title'];
foreach ($title_labels as $bundle => $label) {
  $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $bundle);
  $fields['title']->getConfig($bundle)->setLabel($label)->save();
  $fields['promote']->getConfig($bundle)->setDefaultValue(FALSE)->save();
}
echo "Content model ready. No content, terms, Views, or roles were created.\n";
