# Deep Cuts Basic page recipe

Adapted from `core/recipes/page_content_type` in Drupal 11.4.12. The files in
`config/` are unchanged copies of that recipe's configuration.

The core recipe uses strict configuration comparison by default. On this site,
existing shared node view modes use `en-gb` rather than the shipped `en`, so the
core recipe refuses to apply. This recipe sets `config.strict: false` to import
only missing configuration and preserve existing configuration, including the
shared body field storage. It does not reconcile existing conflicting settings.

Apply from the project root:

```sh
ddev drush recipe recipes/deepcuts_page_content_type -y
ddev drush cr
```

Review the upstream recipe when upgrading Drupal if reusing this adaptation.
