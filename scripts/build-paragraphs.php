<?php

/**
 * @file
 * Create page sections, controlled listings and the Repertoire page content.
 * Run: ddev drush php:script scripts/build-paragraphs.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Entity\View;

$types = ['text' => 'Text', 'image_text' => 'Image and text', 'card_group' => 'Card group', 'reviews' => 'Reviews', 'page_breaker' => 'Page breaker', 'content_listing' => 'Content listing'];
foreach ($types as $id => $label) {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create(['id' => $id, 'label' => $label, 'description' => 'Deep Cuts reusable ' . strtolower($label) . ' section.'])->save();
  }
}
$field = static function ($entity, $bundle, $name, $label, $type, $settings = [], $storage_settings = [], $cardinality = 1, $required = FALSE, $default = NULL) {
  $storage = FieldStorageConfig::loadByName($entity, $name);
  if ($storage && ($storage->getType() !== $type || $storage->getCardinality() !== $cardinality)) {
    throw new RuntimeException("Incompatible field $entity.$name");
  }
  if (!$storage) {
    FieldStorageConfig::create(['entity_type' => $entity, 'field_name' => $name, 'type' => $type, 'cardinality' => $cardinality, 'settings' => $storage_settings, 'translatable' => TRUE])->save();
  }
  if (!FieldConfig::loadByName($entity, $bundle, $name)) {
    $config = FieldConfig::create(['entity_type' => $entity, 'bundle' => $bundle, 'field_name' => $name, 'label' => $label, 'settings' => $settings, 'required' => $required, 'translatable' => TRUE]);
    if ($default !== NULL) { $config->setDefaultValue($default); }
    $config->save();
  }
};
$list = static fn ($values) => ['allowed_values' => $values];
$ref = static fn ($entity, $bundles) => ['handler' => 'default:' . $entity, 'handler_settings' => ['target_bundles' => array_combine($bundles, $bundles), 'auto_create' => FALSE]];
foreach ($types as $bundle => $label) {
  $field('paragraph', $bundle, 'field_heading', 'Heading', 'string', [], ['max_length' => 255]);
  $field('paragraph', $bundle, 'field_background', 'Background', 'list_string', [], $list(['white' => 'White', 'purple-50' => 'Pale lavender', 'purple-100' => 'Lavender', 'orange-50' => 'Pale gold', 'brand' => 'Brand purple', 'dark' => 'Black']), 1, TRUE, 'white');
  $field('paragraph', $bundle, 'field_width', 'Content width', 'list_string', [], $list(['standard' => 'Standard', 'narrow' => 'Narrow']), 1, TRUE, 'standard');
  if (in_array($bundle, ['text', 'image_text', 'page_breaker'])) {
    $field('paragraph', $bundle, 'field_text', 'Text', 'text_long', [], [], 1, $bundle !== 'page_breaker');
  }
  if (in_array($bundle, ['text', 'card_group'])) {
    $field('paragraph', $bundle, 'field_columns', 'Columns on desktop', 'list_string', [], $list(['1' => 'One', '2' => 'Two', '3' => 'Three']), 1, TRUE, $bundle === 'text' ? '1' : '3');
  }
  if ($bundle === 'text') {
    $field('paragraph', $bundle, 'field_bordered', 'Bordered introduction', 'boolean', ['on_label' => 'Bordered', 'off_label' => 'Plain'], [], 1, FALSE, 0);
  }
  if (in_array($bundle, ['image_text', 'page_breaker'])) {
    $field('paragraph', $bundle, 'field_link', 'Button', 'link', ['title' => 2, 'link_type' => 17]);
  }
  if ($bundle === 'image_text') {
    $field('paragraph', $bundle, 'field_section_image', 'Image', 'entity_reference', $ref('media', ['image']), ['target_type' => 'media'], 1, TRUE);
    $field('paragraph', $bundle, 'field_image_position', 'Image position on desktop', 'list_string', [], $list(['left' => 'Left', 'right' => 'Right']), 1, TRUE, 'left');
  }
  if (in_array($bundle, ['card_group', 'reviews'])) {
    $bundles = $bundle === 'reviews' ? ['review'] : ['package', 'add_on', 'band_member', 'article'];
    $field('paragraph', $bundle, 'field_items', $bundle === 'reviews' ? 'Selected reviews (leave empty for featured reviews)' : 'Selected cards', 'entity_reference', $ref('node', $bundles), ['target_type' => 'node'], -1, $bundle === 'card_group');
  }
  if ($bundle === 'reviews') {
    $field('paragraph', $bundle, 'field_layout', 'Layout', 'list_string', [], $list(['grid' => 'Grid', 'carousel' => 'Swipeable carousel']), 1, TRUE, 'carousel');
  }
  if ($bundle === 'content_listing') {
    $field('paragraph', $bundle, 'field_listing', 'Listing', 'list_string', [], $list(['song' => 'Songs', 'faq' => 'FAQs', 'package' => 'Packages', 'add_on' => 'Add-ons', 'band_member' => 'Band members', 'video' => 'Videos']), 1, TRUE, 'song');
    $field('paragraph', $bundle, 'field_category', 'Repertoire category (songs only)', 'entity_reference', $ref('taxonomy_term', ['repertoire_category']), ['target_type' => 'taxonomy_term']);
  }
  $form = EntityFormDisplay::load("paragraph.$bundle.default") ?? EntityFormDisplay::create(['targetEntityType' => 'paragraph', 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE]);
  $view = EntityViewDisplay::load("paragraph.$bundle.default") ?? EntityViewDisplay::create(['targetEntityType' => 'paragraph', 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE]);
  $weight = 0;
  foreach (['field_heading', 'field_text', 'field_section_image', 'field_image_position', 'field_items', 'field_listing', 'field_category', 'field_layout', 'field_link', 'field_columns', 'field_bordered', 'field_background', 'field_width'] as $name) {
    $f = FieldConfig::loadByName('paragraph', $bundle, $name);
    if (!$f) { continue; }
    $widget = match ($f->getType()) {
      'text_long' => 'text_textarea', 'list_string' => 'options_select', 'boolean' => 'boolean_checkbox', 'link' => 'link_default',
      'entity_reference' => $f->getFieldStorageDefinition()->getSetting('target_type') === 'media' ? 'media_library_widget' : 'entity_reference_autocomplete',
      default => 'string_textfield',
    };
    $form->setComponent($name, ['type' => $widget, 'weight' => $weight++]);
    if ($name === 'field_text') { $view->setComponent($name, ['type' => 'text_default', 'label' => 'hidden']); }
    elseif ($name === 'field_section_image') { $view->setComponent($name, ['type' => 'entity_reference_entity_view', 'label' => 'hidden', 'settings' => ['view_mode' => 'default', 'link' => FALSE]]); }
    else { $view->removeComponent($name); }
  }
  $form->save(); $view->save();
}
$field('node', 'page', 'field_sections', 'Page sections', 'entity_reference_revisions', $ref('paragraph', array_keys($types)), ['target_type' => 'paragraph'], -1);
$form = EntityFormDisplay::load('node.page.default');
$form->setComponent('field_sections', ['type' => 'paragraphs', 'weight' => 10, 'settings' => ['title' => 'Section', 'title_plural' => 'Sections', 'edit_mode' => 'closed', 'closed_mode' => 'summary', 'autocollapse' => 'all', 'add_mode' => 'dropdown', 'form_display_mode' => 'default', 'default_paragraph_type' => 'text']])->save();
$view = EntityViewDisplay::load('node.page.default');
$view->setComponent('field_sections', ['type' => 'entity_reference_revisions_entity_view', 'label' => 'hidden', 'weight' => 10, 'settings' => ['view_mode' => 'default', 'link' => FALSE]])->save();

// Controlled listing Views. Entity rendering retains access checks/cache tags.
foreach (['song', 'faq', 'package', 'add_on', 'band_member', 'review', 'video'] as $bundle) {
  $id = 'deepcuts_' . $bundle;
  if (View::load($id)) { continue; }
  $media = $bundle === 'video';
  $entity = $media ? 'media' : 'node';
  $table = $media ? 'media_field_data' : 'node_field_data';
  $options = [
    'access' => ['type' => 'perm', 'options' => ['perm' => 'access content']],
    'cache' => ['type' => 'tag'], 'pager' => ['type' => 'none'],
    'style' => ['type' => 'default', 'options' => ['row_class' => 'dc-listing-row']],
    'row' => ['type' => 'entity:' . $entity, 'options' => ['view_mode' => $media ? 'default' : 'dc_card']],
    'filters' => [
      'status' => ['id' => 'status', 'table' => $table, 'field' => 'status', 'value' => '1', 'entity_type' => $entity, 'entity_field' => 'status', 'plugin_id' => 'boolean'],
      'type' => ['id' => $media ? 'bundle' : 'type', 'table' => $table, 'field' => $media ? 'bundle' : 'type', 'value' => $media ? ['video' => 'video', 'remote_video' => 'remote_video'] : [$bundle => $bundle], 'entity_type' => $entity, 'entity_field' => $media ? 'bundle' : 'type', 'plugin_id' => 'bundle'],
    ],
    'sorts' => [], 'title' => '',
  ];
  if (!$media) {
    $options['sorts']['field_display_order_value'] = ['id' => 'field_display_order_value', 'table' => 'node__field_display_order', 'field' => 'field_display_order_value', 'order' => 'ASC', 'plugin_id' => 'standard'];
    $options['sorts']['nid'] = ['id' => 'nid', 'table' => $table, 'field' => 'nid', 'order' => 'ASC', 'entity_type' => 'node', 'entity_field' => 'nid', 'plugin_id' => 'standard'];
  }
  if ($bundle === 'review') {
    $options['filters']['field_featured_value'] = ['id' => 'field_featured_value', 'table' => 'node__field_featured', 'field' => 'field_featured_value', 'value' => '1', 'plugin_id' => 'boolean'];
  }
  if ($bundle === 'song') {
    $options['arguments']['field_repertoire_category_target_id'] = [
      'id' => 'field_repertoire_category_target_id', 'table' => 'node__field_repertoire_category', 'field' => 'field_repertoire_category_target_id',
      'plugin_id' => 'taxonomy_index_tid', 'default_action' => 'ignore', 'break_phrase' => FALSE,
      'validate' => ['type' => 'none', 'fail' => 'not found'],
    ];
  }
  View::create(['id' => $id, 'label' => 'Deep Cuts: ' . $bundle, 'status' => TRUE, 'base_table' => $table, 'base_field' => $media ? 'mid' : 'nid', 'display' => ['default' => ['id' => 'default', 'display_title' => 'Default', 'display_plugin' => 'default', 'position' => 0, 'display_options' => $options]]])->save();
}
if (!EntityViewMode::load('node.dc_card')) {
  EntityViewMode::create(['id' => 'node.dc_card', 'label' => 'Deep Cuts card', 'targetEntityType' => 'node', 'status' => TRUE])->save();
}
foreach (['song', 'review', 'faq', 'package', 'add_on', 'band_member', 'article'] as $bundle) {
  $display = EntityViewDisplay::load("node.$bundle.dc_card") ?? EntityViewDisplay::create(['targetEntityType' => 'node', 'bundle' => $bundle, 'mode' => 'dc_card', 'status' => TRUE]);
  foreach (FieldConfig::loadMultiple() as $f) {
    if ($f->getTargetEntityTypeId() !== 'node' || $f->getTargetBundle() !== $bundle) { continue; }
    $name = $f->getName();
    if (in_array($name, ['body', 'field_quote', 'field_reviewer', 'field_answer', 'field_artist', 'field_role', 'field_price_wording', 'field_image'])) {
      $type = match ($f->getType()) { 'text_with_summary' => 'text_summary_or_trimmed', 'text_long' => 'text_default', 'entity_reference' => 'entity_reference_entity_view', default => 'string' };
      $display->setComponent($name, ['type' => $type, 'label' => 'hidden', 'settings' => $type === 'entity_reference_entity_view' ? ['view_mode' => 'default', 'link' => FALSE] : []]);
    }
    else { $display->removeComponent($name); }
  }
  $display->save();
}

$page_ids = \Drupal::entityQuery('node')->condition('type', 'page')->condition('title', 'Repertoire')->accessCheck(FALSE)->execute();
if (count($page_ids) !== 1) { throw new RuntimeException('Expected one Repertoire page.'); }
$page = Node::load(reset($page_ids));
if (!$page->get('field_sections')->isEmpty()) {
  echo "Existing Repertoire sections preserved.\n";
  return;
}
$data = json_decode(file_get_contents(__DIR__ . '/data/repertoire.json'), TRUE, 512, JSON_THROW_ON_ERROR);
$sections = [];
$create = static function ($type, $values) use (&$sections) {
  $p = Paragraph::create(['type' => $type] + $values);
  $p->save(); $sections[] = $p;
};
$create('text', [
  'field_heading' => 'Chart-toppers and deep cuts', 'field_bordered' => TRUE,
  'field_text' => ['value' => 'With a mix of modern bangers, 90s & 00s hits, and vintage & classics, our repertoire has both the range and the depth to delight any crowd. The Deep Cuts spirit is to perform songs that don’t usually crop up at weddings and big occasions, so you can be assured that our repertoire will be truly unique at your event. And that’s not to forget our acoustic repertoire, specially curated for atmospheric vibes.', 'format' => 'basic_html'],
]);
$count = 0;
foreach ($data as $category => $songs) {
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'repertoire_category', 'name' => $category]);
  $term = $terms ? reset($terms) : Term::create(['vid' => 'repertoire_category', 'name' => $category]);
  if ($term->isNew()) { $term->save(); }
  foreach ($songs as $order => $line) {
    [$title, $artist] = explode(' – ', $line, 2);
    $exists = \Drupal::entityQuery('node')->condition('type', 'song')->condition('title', $title)->condition('field_artist', $artist)->condition('field_repertoire_category', $term->id())->accessCheck(FALSE)->execute();
    if (!$exists) {
      Node::create(['type' => 'song', 'title' => $title, 'status' => TRUE, 'uid' => 1, 'field_artist' => $artist, 'field_repertoire_category' => $term->id(), 'field_display_order' => $order, 'promote' => FALSE])->save();
    }
    $count++;
  }
  $create('content_listing', ['field_heading' => $category, 'field_listing' => 'song', 'field_category' => $term->id(), 'field_width' => 'narrow', 'field_background' => count($sections) % 2 ? 'purple-50' : 'purple-100']);
}
$create('page_breaker', [
  'field_heading' => '“Do you know how to play...?”', 'field_background' => 'purple-50',
  'field_text' => ['value' => 'Is there a song you’d love played at your event? Not to worry – we offer an Additional Song service where we’ll learn your chosen tune, so you can dance the night away to your perfect first dance or celebration song. And a bonus: the first song we learn this way is free of charge.', 'format' => 'basic_html'],
]);
$page->set('field_sections', array_map(static fn ($p) => ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()], $sections));
$page->setNewRevision(TRUE);
$page->setRevisionLogMessage('Build Repertoire with Figma copy, structured Songs and reusable Paragraphs.');
$page->save();
echo "Built six Repertoire sections and imported $count songs.\n";
