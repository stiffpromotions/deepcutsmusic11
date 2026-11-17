# Deep Cuts 11 theme

[Bootstrap 5](https://www.drupal.org/project/bootstrap5) subtheme.

## Development.

### CSS compilation.

Prerequisites: install [sass](https://sass-lang.com/install).

To compile, run from the subtheme directory:

```sh
sass scss/_fonts.scss css/fonts.css
sass scss/style.scss css/style.css
sass scss/ck5style.scss css/ck5style.css
```

### Typography

Rethink Sans is self-hosted in `fonts/rethink-sans/` with its SIL Open Font
License. Normal weights 400, 500, 600, and 700 were downloaded from the Google
Fonts CSS API on 9 November 2026. There are no runtime Google Fonts requests.
Font faces are compiled separately so they load globally in both the site and
CKEditor. Keep the font assets, licence, and compiled CSS in Git.

`scss/_variables_bootstrap.scss` sets the family, body size/leading, and heading
sizes before Bootstrap compilation. `scss/_typography.scss` adds Figma's heading
weights, leading, and letter spacing. Desktop sizes are Hero 56px, H1 48px,
H2 36px, H3 30px, Body 16px, Small 14px, and Navigation 16px, assuming a 16px
browser root size. Bootstrap RFS scales heading sizes on smaller screens.

Use `.hero-title` for hero headings and `.site-navigation` or `.navigation-text`
for the navigation style. These classes can be attached when building templates;
navigation styling is not applied to Drupal admin toolbars or local task tabs.
Body and small text use weight 400, navigation 500, H3 600, and H1/H2/Hero 700.
Figma's centred specimen text does not impose alignment on all site content.

### Colours and components

All palette values and Bootstrap colour overrides live in
`scss/_variables_bootstrap.scss`. The palette's brand purple is `#572998`;
Figma button components use `#512B92`. Both are retained intentionally.

- Use `btn btn-nav`, `btn btn-primary`, or `btn btn-secondary` for Figma's
  navigation, primary, or secondary buttons. Primary CTA buttons are gold even
  though Bootstrap's semantic primary colour is brand purple.
- `_links.scss` styles content link decoration and keyboard focus.
- `_pager.scss` styles Bootstrap5's Drupal pager classes with palette-based
  active, hover, focus, and disabled colours.
- Tinted surfaces: `bg-purple-50`, `bg-purple-100`, `bg-purple-200`, `bg-orange-50`,
  `bg-orange-200`, and `bg-orange-500`.
- `surface-brand` and `surface-dark` provide backgrounds, light heading/text
  colours, and light links. `text-accent` and `text-pod` use muted text colour.

Review `/docs/theme-components.html` on the local site. Pager, keyboard-focus,
disabled, and content-link states are palette-based extensions. The gold button
light-text combination follows Figma and should be reviewed for contrast before
launch. No layout or navigation templates are changed by these foundations.
