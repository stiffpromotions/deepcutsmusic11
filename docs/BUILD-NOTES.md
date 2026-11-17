# Design and build requirements

Recorded 9 November 2026 from the user-supplied project correspondence,
including Florentyna's email of 8 November 2026 and Iain's follow-up.
These notes guide future implementation; they do not indicate completed work.

## Design references and assets

- [Figma Sites: Deep Cuts WIP](https://www.figma.com/site/aAc1XxTKy3rMrlThVH8gj2/Deep-Cuts-WIP?node-id=0-1).
- Use the components frame, actual page designs, and component formatting as
  the primary implementation references. The Styles frame contains setup styles
  that are not all used in the final designs; do not assume every style is needed.
- [Design-sized images and band logos on Dropbox](https://www.dropbox.com/scl/fo/ccf5f1n5b3aivxq6i73tj/ABBg6L6uiSaqtUUc0cE4pbk?rlkey=w0bhbp8jftzrrzprx9y370ioe&dl=0).
  Originals, alternative sizes, and other logo colourways can be requested from
  Florentyna. The design uses the cream logo.
- Florentyna reports an edit invitation to the user's info@ account. Access and
  seat permissions must be checked when needed; earlier inspection was view-only.

## Contact / Book now form

- Reuse the existing Stiff Promotions contact form/template as the starting point.
- Remove the **Band Preference** field, as explicitly requested by Iain.
- Apply the Deep Cuts fonts and design styling.
- Inspect the source form before implementation; its remaining fields, handlers,
  validation, and delivery settings have not yet been reviewed here.

## Homepage promo video

- The opening homepage visual should be the electric promo video.
- Autoplay muted, with a visible option for the visitor to unmute.
- Implement and verify browser autoplay behaviour and appropriate fallback before
  treating the feature as complete.
- Promo video uploads/YouTube URLs were pending in Florentyna's email. Iain
  reports that manager access has since been granted to the Stiff Promotions
  YouTube channel. This is reported access, not independently verified access or
  confirmation that uploads are complete. Check the channel/URLs when required.

## Navigation header

- Start transparent over the page's opening visual.
- On scroll, transition to the primary purple background with white text.
- Build a reusable header rather than reproducing Figma's per-page workaround.
- The Figma limitation is a design-tool limitation, not a requirement for a
  static website header.

## Privacy and cookie policy pages

- Content was adapted from Stiff Promotions with some changes.
- Restore the table structure/formatting lost during copying into the design.
- Review the supplied page content and original table structure during the build.

## Responsive layouts

- No dedicated mobile or tablet designs have been supplied.
- Develop responsive layouts from the desktop design using project judgment and
  user steering. Ask Florentyna for additional design input if needed.

## Packages and add-ons

- Build the current text-heavy Packages design first.
- Options & Add-ons should change colour on hover; include keyboard-focus
  treatment when implementing interactive elements.
- A more graphical Packages redesign is a later enhancement, not a launch-build
  prerequisite in these notes.

## Copy and search work

- Existing copy prioritises human readers and is intentional.
- Background SEO/AI experimentation is an idea raised by Florentyna, not an
  agreed implementation scope. Discuss any proposed copy changes with the user;
  Florentyna is available to help with edits if needed.
# Policy content review before launch

- Privacy and Cookie policies were imported from Figma, with their supplied
  effective dates retained. Their statements describe intended services, not an
  audit of the current build. Reconcile analytics, newsletter, embedded content,
  cookie consent and data-processing statements against the final integrations.
- The theme self-hosts Rethink Sans. The supplied references to Google Fonts
  tracking/cookies need correcting when the policy wording is reviewed.
- Figma's flattened cookie-purpose table ends with the incomplete fragment
  “Google Fon”. Only complete rows were reconstructed; the complete Google Fonts
  entry in the Cookie details table remains as supplied pending review.

