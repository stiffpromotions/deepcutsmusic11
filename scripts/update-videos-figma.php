<?php

/**
 * @file
 * Restore Figma content/layout: ddev drush php:script scripts/update-videos-figma.php
 * Add missing intro/credits; preserve existing copy, video URLs and section order.
 */

$path = \Drupal::service('path_alias.manager')->getPathByAlias('/videos');
if (!preg_match('@^/node/(\d+)$@', $path, $match) || !($videos_page = \Drupal\node\Entity\Node::load($match[1]))) {
  throw new \RuntimeException('Create the Videos page first.');
}
$data = json_decode(file_get_contents(__DIR__ . '/data/videos.json'), TRUE, 512, JSON_THROW_ON_ERROR);
$paragraphs = $videos_page->get('field_sections')->referencedEntities();
$has_intro = $has_credits = FALSE;
foreach ($paragraphs as $paragraph) {
  $heading = $paragraph->hasField('field_heading') ? $paragraph->get('field_heading')->value : '';
  $has_intro = $has_intro || $heading === 'Recorded LIVE';
  $has_credits = $has_credits || $heading === 'Credits';
}
if ($has_intro && $has_credits) {
  echo "Videos: Figma sections already present; editorial content preserved.\n";
  return;
}
$escape = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
if (!$has_intro) {
  // Only migrate the old tinted layout when introducing the missing Figma intro.
  foreach ($paragraphs as $paragraph) {
    if ($paragraph->bundle() === 'video') {
      $paragraph->setNewRevision(TRUE);
      $paragraph->set('field_background', 'white');
      $paragraph->save();
    }
  }
  $intro = \Drupal\paragraphs\Entity\Paragraph::create([
    'type' => 'text', 'field_heading' => 'Recorded LIVE', 'field_background' => 'white',
    'field_bordered' => TRUE, 'field_width' => 'standard', 'field_columns' => '1',
    'field_text' => ['value' => '<p>' . $escape($data['intro']) . '</p>', 'format' => 'basic_html'],
  ]);
  $intro->save();
  array_unshift($paragraphs, $intro);
}
if (!$has_credits) {
  $credits = \Drupal\paragraphs\Entity\Paragraph::create([
    'type' => 'page_breaker', 'field_heading' => 'Credits', 'field_background' => 'purple-50', 'field_width' => 'standard',
    'field_text' => ['value' => '<p>' . $escape($data['credits_intro']) . '</p><p>' . implode('<br>', array_map($escape, $data['credits'])) . '</p>', 'format' => 'basic_html'],
  ]);
  $credits->save();
  $paragraphs[] = $credits;
}
$references = array_map(static fn ($paragraph) => ['target_id' => $paragraph->id(), 'target_revision_id' => $paragraph->getRevisionId()], $paragraphs);
$videos_page->set('field_sections', $references);
$videos_page->setNewRevision(TRUE);
$videos_page->setRevisionLogMessage('Match Figma Videos: bordered Recorded LIVE introduction, white video sections and full-width Credits. Retain replacement YouTube videos.');
$videos_page->save();
echo "Videos: restored Figma introduction, white video sections and Credits; video Media retained.\n";
