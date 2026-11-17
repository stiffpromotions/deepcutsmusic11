# Maintenance mode and Editor access

The Deep Cuts theme provides a branded maintenance page with a cream logo,
purple panel, gold **Admin login** button and responsive layout. Its message is
editable through Drupal's standard Maintenance mode settings. The title and
surrounding copy live in `templates/layout/maintenance-page.html.twig`; styling
is in `scss/_maintenance.scss`, compiled through `style.scss`.

## Using maintenance mode for beta preview

As an administrator, open `/admin/config/development/maintenance`, enable
**Put site into maintenance mode** and save. Anonymous visitors receive the
themed HTTP 503 page with noindex metadata. The login button goes to
`/user/login?destination=/`, returning permitted users to the homepage after
sign-in. Drupal's login, password-request and reset routes remain available.

Users with the **Editor** role have **Use the site in maintenance mode**, so they
can browse the beta website and manage content after signing in. Other logged-in
users without that permission still receive the maintenance page. Editors cannot
turn maintenance mode on or off. An administrator must disable it for launch.

Maintenance mode was **not enabled** by this change. The default theme remains
Deep Cuts, which Drupal uses for maintenance mode; no install/update theme
override was added.

## Editor role

Role machine name: `editor`; display name: **Editor**. The pre-existing
`content_editor` role is left unchanged.

Editors can create, edit and delete all eight existing node types, including
content created by another author; manage their Paragraphs sections; create,
edit and delete Media; manage terms in existing vocabularies; edit page metadata,
sharing images and optional editorial SEO guidance; create content aliases; and
view/revert content revisions. Basic/restricted HTML is allowed, while unrestricted
Full HTML is not granted. Editors have the content overview, toolbar and Gin
administration theme needed to manage content.

The role does not grant site configuration, content-type/field/display setup,
Views, taxonomy-vocabulary setup, Media/Paragraph type setup, modules, themes,
permissions, user management, menus, global SEO settings or Webform definition
administration. Booking-form definitions and delivery settings remain an
administrator's responsibility. New content types, Media types or vocabularies
will need a deliberate permission review.

## Iain account

Created one active account with username **Iain**, email
**iain@stiffpromotions.co.uk**, and the **Editor** role. Its initial password is
randomly generated and was never printed, saved in project documentation or
emailed. Use `/user/password` to request a password-setting link, or have the
administrator set an initial password in the account editing screen. No welcome
or reset email was sent as part of this task.

Local DDEV email is captured by Mailpit, so it will not reach Iain's real inbox.
Before inviting him on the public beta, deploy/create the account there and check
production password-reset delivery. User records and passwords are database
content; they do not transfer through configuration import.

## Deployment and reproduction

The new role and maintenance message are exported to `sync/user.role.editor.yml`
and `sync/system.maintenance.yml`. Deploy the theme changes and import normal
configuration. Transfer the user through the database/content workflow, or run
`drush php:script scripts/setup-editor.php` on the target to create the requested
account. The setup script preserves an existing matching account/password and
refuses conflicting identities or accounts with unexpected extra roles. It sends
no email and does not change maintenance state. It reapplies the role policy and
message, so treat it as a setup utility, not a routine deployment hook.

## Verification

`ddev drush php:script scripts/verify-editor-maintenance.php` checks the account,
create/edit/delete access for all existing node types, Media creation, page
editing controls, and denial of configuration/user-management routes. An isolated
in-memory maintenance state exercises Drupal's actual maintenance response
renderer and verifies the 503 status, title, noindex, message and login button.
Login/password/reset exemptions and Editor/anonymous maintenance access are also
checked. The real maintenance state is never changed by the test.

Safari desktop and 375px visual QA passed. The temporary preview artifact was
removed. No real password reset or email delivery was tested.
