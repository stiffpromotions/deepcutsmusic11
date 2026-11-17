<?php

/**
 * @file
 * Apply the approved booking email notification settings.
 * Run: ddev drush php:script scripts/configure-booking-email.php
 */

use Drupal\webform\Entity\Webform;

$form = Webform::load('booking');
if (!$form) {
  throw new \RuntimeException('Build the booking form first.');
}
$configuration = [
  'id' => 'email',
  'handler_id' => 'email_notification',
  'label' => 'Booking enquiry notification',
  'status' => TRUE,
  'weight' => 0,
  'conditions' => [],
  'settings' => [
    'states' => ['completed'],
    'to_mail' => 'iain@stiffpromotions.co.uk',
    'bcc_mail' => 'info@alimartin.com',
    'cc_mail' => '',
    'from_mail' => 'noreply@deepcutsmusic.co.uk',
    'from_name' => '* Deep Cuts website *',
    'reply_to' => 'noreply@deepcutsmusic.co.uk',
    'subject' => '* Enquiry from Deep Cuts website *',
    'body' => '<h2>New booking enquiry</h2>[webform_submission:values]',
    'html' => TRUE,
    'exclude_empty' => FALSE,
    'exclude_empty_checkbox' => FALSE,
    'attachments' => FALSE,
    'twig' => FALSE,
    'debug' => FALSE,
  ],
];
if ($form->getHandlers()->has('email_notification')) {
  $handler = $form->getHandler('email_notification');
  $existing = $handler->getConfiguration();
  $configuration['settings'] += $existing['settings'];
  $handler->setConfiguration($configuration);
}
else {
  $handler = \Drupal::service('plugin.manager.webform.handler')->createInstance('email', $configuration);
  $form->addWebformHandler($handler);
}
$form->save();
$stored = Webform::load('booking')->getHandler('email_notification')->getConfiguration();
foreach ($configuration['settings'] as $key => $value) {
  if ($stored['settings'][$key] !== $value) {
    throw new \RuntimeException("Email setting verification failed: $key");
  }
}
echo "Verified enabled HTML notification for completed submissions, recipient, BCC, sender, reply-to and subject. No email sent.\n";
