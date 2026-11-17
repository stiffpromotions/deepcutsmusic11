<?php

$settings['update_free_access'] = FALSE;
$settings['file_private_path'] = DRUPAL_ROOT . '/private-files';
$settings['file_temp_path'] = DRUPAL_ROOT . '/tmp';
$settings['config_sync_directory'] = DRUPAL_ROOT . '/sync';
$settings['enable_html5_validation'] = FALSE;

$settings['trusted_host_patterns'] = [
  '^deepcutsmusic\.co\.uk$',
  '^www\.deepcutsmusic\.co\.uk$',
  '^.+\.deepcutsmusic\.uk$',
  '^deepcutsmusic11\.ddev\.site$',
  '^.+\.deepcutsmusic11\.ddev\.site$',
];

// Enable only on the public production installation when final content is ready.
// Local/staging remain noindex even if they receive a copy of the live database.
$settings['deepcuts_seo_indexing_enabled'] = getenv('DEEPCUTS_SEO_PUBLIC') === '1'
  && getenv('IS_DDEV_PROJECT') !== 'true';
