<?php

/**
 * @file
 * Build FAQs and policies: ddev drush php:script scripts/build-information-pages.php
 * Existing FAQ nodes and page sections are preserved on repeat runs.
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Entity\View;

$category = FieldConfig::loadByName('paragraph', 'content_listing', 'field_category');
$settings = $category->getSetting('handler_settings');
$settings['target_bundles']['faq_category'] = 'faq_category';
$category->set('label', 'Category (songs or FAQs)')->setSetting('handler_settings', $settings)->save();
$view = View::load('deepcuts_faq');
$displays = $view->get('display');
$displays['default']['display_options']['arguments']['field_faq_category_target_id'] = [
  'id' => 'field_faq_category_target_id', 'table' => 'node__field_faq_category',
  'field' => 'field_faq_category_target_id', 'plugin_id' => 'taxonomy_index_tid',
  'default_action' => 'ignore', 'break_phrase' => FALSE,
  'validate' => ['type' => 'none', 'fail' => 'not found'],
];
$view->set('display', $displays)->save();

$load_page = static function (string $alias): Node {
  $path = \Drupal::service('path_alias.manager')->getPathByAlias($alias);
  if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($page = Node::load($match[1]))) {
    throw new \RuntimeException("Create $alias using build-site-pages.php first.");
  }
  return $page;
};
$hero = $load_page('/repertoire')->get('field_hero_image')->getValue();
$save_sections = static function (Node $page, array $values) use ($hero): void {
  if (!$page->get('field_sections')->isEmpty()) {
    echo $page->label() . ": existing sections preserved.\n";
    return;
  }
  $sections = [];
  foreach ($values as $value) {
    $p = Paragraph::create($value);
    $p->save();
    $sections[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
  }
  $page->set('field_sections', $sections);
  if ($page->get('field_hero_image')->isEmpty()) { $page->set('field_hero_image', $hero); }
  $page->setNewRevision(TRUE);
  $page->setRevisionLogMessage('Build information page from Figma source copy.');
  $page->save();
  echo $page->label() . ": sections created.\n";
};

$data = json_decode(file_get_contents(__DIR__ . '/data/faqs.json'), TRUE, 512, JSON_THROW_ON_ERROR);
$sections = [];
$order = 0;
foreach ($data as $group => $questions) {
  $existing = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'faq_category', 'name' => $group]);
  $term = $existing ? reset($existing) : Term::create(['vid' => 'faq_category', 'name' => $group, 'weight' => count($sections)]);
  if ($term->isNew()) { $term->save(); }
  foreach ($questions as [$question, $answer]) {
    $existing = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'faq', 'title' => $question]);
    if (!$existing) {
      Node::create([
        'type' => 'faq', 'title' => $question, 'status' => TRUE, 'uid' => 1, 'promote' => FALSE,
        'field_answer' => ['value' => '<p>' . $answer . '</p>', 'format' => 'basic_html'],
        'field_faq_category' => $term->id(), 'field_display_order' => $order,
      ])->save();
    }
    $order++;
  }
  $sections[] = ['type' => 'content_listing', 'field_heading' => $group, 'field_listing' => 'faq', 'field_category' => $term->id(), 'field_width' => 'narrow'];
}
$sections[] = ['type' => 'page_breaker', 'field_heading' => 'Have another question?', 'field_background' => 'purple-50', 'field_link' => ['uri' => 'internal:/book-now', 'title' => 'Contact Us']];
$save_sections($load_page('/faqs'), $sections);
foreach (['privacy-policy', 'cookie-policy'] as $policy) {
  $html = file_get_contents(__DIR__ . '/data/' . $policy . '.html');
  $save_sections($load_page('/' . $policy), [[
    'type' => 'text', 'field_text' => ['value' => '<div class="dc-policy">' . $html . '</div>', 'format' => 'full_html'],
  ]]);
}
foreach (['field.field.paragraph.content_listing.field_category', 'views.view.deepcuts_faq'] as $name) {
  file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
}
echo "$order FAQs in four categories; policy wording imported from Figma.\n";
