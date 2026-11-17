<?php

/**
 * @file
 * Configure reusable responsive hero image styles and focal-point editing.
 * Run: ddev drush php:script scripts/setup-hero.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\image\Entity\ImageStyle;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;

foreach ([
  'hero_mobile_480' => [480, 320],
  'hero_mobile_768' => [768, 512],
  'hero_mobile_1536' => [1536, 1024],
  'hero_wide_768' => [768, 213],
  'hero_wide_1440' => [1440, 400],
  'hero_wide_2880' => [2880, 800],
] as $id => [$width, $height]) {
  if (!ImageStyle::load($id)) {
    $style = ImageStyle::create(['name' => $id, 'label' => "Hero $width × $height"]);
    $style->addImageEffect([
      'id' => 'focal_point_scale_and_crop', 'weight' => 0,
      'data' => ['width' => $width, 'height' => $height, 'crop_type' => 'focal_point'],
    ]);
    $style->save();
  }
}
if (!ResponsiveImageStyle::load('deepcuts_hero')) {
  $style = ResponsiveImageStyle::create([
    'id' => 'deepcuts_hero', 'label' => 'Deep Cuts page hero',
    'breakpoint_group' => 'deepcutsmusic11.screen',
    'fallback_image_style' => 'hero_wide_1440',
  ]);
  foreach ([
    'deepcutsmusic11.screen.sm-max' => ['hero_mobile_480', 'hero_mobile_768', 'hero_mobile_1536'],
    'deepcutsmusic11.screen.md-min' => ['hero_wide_768', 'hero_wide_1440', 'hero_wide_2880'],
  ] as $breakpoint => $styles) {
    $style->addImageStyleMapping($breakpoint, '1x', [
      'image_mapping_type' => 'sizes',
      'image_mapping' => ['sizes' => '100vw', 'sizes_image_styles' => $styles],
    ]);
  }
  $style->save();
}
foreach (['default', 'media_library'] as $mode) {
  $display = EntityFormDisplay::load('media.image.' . $mode);
  if ($display && ($component = $display->getComponent('field_media_image'))) {
    $display->setComponent('field_media_image', [
      'type' => 'image_focal_point', 'weight' => $component['weight'],
      'settings' => ['preview_image_style' => 'medium', 'preview_link' => TRUE, 'offsets' => '50,50', 'progress_indicator' => 'throbber'],
    ])->save();
  }
}
echo "Hero responsive styles and Media focal-point widgets configured.\n";
