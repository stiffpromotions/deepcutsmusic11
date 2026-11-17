<?php

/**
 * @file
 * Build the booking page: ddev drush php:script scripts/build-booking-page.php.
 * Preserves existing form settings/handlers and page sections on repeat runs.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\webform\Entity\Webform;

$path = \Drupal::service('path_alias.manager')->getPathByAlias('/book-now');
if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($page = Node::load($match[1]))) {
  throw new \RuntimeException('Create the Book now page first.');
}
$form = Webform::load('booking');
if (!$form) {
  $form = Webform::create(['id' => 'booking', 'title' => 'Booking enquiry', 'status' => 'open']);
  $form->setElements(\Drupal\Component\Serialization\Yaml::decode(file_get_contents(__DIR__ . '/data/booking-elements.yml')));
  $form->setSettings([
    'page' => FALSE,
    'form_title' => 'none',
    'form_previous_submissions' => FALSE,
    'form_attributes' => ['class' => ['dc-booking-form']],
    'confirmation_type' => 'url',
    'confirmation_url' => '/thank-you',
    'confirmation_exclude_query' => TRUE,
    'confirmation_exclude_token' => TRUE,
  ]);
  $form->save();
}
if (!FieldStorageConfig::loadByName('node', 'field_booking_form')) {
  FieldStorageConfig::create(['entity_type' => 'node', 'field_name' => 'field_booking_form', 'type' => 'webform', 'cardinality' => 1])->save();
}
if (!FieldConfig::loadByName('node', 'page', 'field_booking_form')) {
  FieldConfig::create(['entity_type' => 'node', 'bundle' => 'page', 'field_name' => 'field_booking_form', 'label' => 'Booking form'])->save();
}
$display = EntityViewDisplay::load('node.page.default');
$display->setComponent('field_booking_form', ['type' => 'webform_entity_reference_entity_view', 'label' => 'hidden', 'weight' => 30, 'settings' => ['source_entity' => TRUE]])->save();
$edit = EntityFormDisplay::load('node.page.default');
$edit->setComponent('field_booking_form', ['type' => 'webform_entity_reference_select', 'weight' => 30])->save();
if ($page->get('field_sections')->isEmpty()) {
  $intro = Paragraph::create([
    'type' => 'text', 'field_heading' => 'Ready to book?', 'field_bordered' => TRUE,
    'field_text' => ['value' => '<p>Fill in the form below and we’ll put together a quote for you. Make sure to include any notes that you think would be useful for us to know, including any special requests.</p>', 'format' => 'basic_html'],
  ]);
  $intro->save();
  $page->set('field_sections', [['target_id' => $intro->id(), 'target_revision_id' => $intro->getRevisionId()]]);
}
$page->set('field_booking_form', ['target_id' => 'booking', 'status' => 'open']);
$page->setTitle('Book Now');
if ($page->get('field_hero_image')->isEmpty()) {
  $hero_path = \Drupal::service('path_alias.manager')->getPathByAlias('/repertoire');
  if (preg_match('@^/node/(\d+)$@', $hero_path, $hero_match) && ($hero = Node::load($hero_match[1]))) {
    $page->set('field_hero_image', $hero->get('field_hero_image')->getValue());
  }
}
$page->setNewRevision(TRUE);
$page->setRevisionLogMessage('Build branded booking enquiry form and Figma introduction.');
$page->save();
echo "Booking page and form ready.\n";
