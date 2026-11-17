<?php
/** @file Verify anonymous validation, persistence and redirect; remove test submissions. */
use Drupal\webform\Entity\WebformSubmission;
// This submits a real disposable record. Do not send test notifications.
$form = \Drupal\webform\Entity\Webform::load('booking');
foreach ($form->getHandlers() as $handler) {
  if ($handler->isEnabled()) {
    throw new \RuntimeException('Submission test requires notification handlers to be disabled in an isolated test environment.');
  }
}
$client = \Drupal::httpClient();
$options = ['headers' => ['Host' => 'deepcutsmusic11.ddev.site'], 'http_errors' => FALSE, 'allow_redirects' => FALSE];
$url = 'http://localhost/book-now';
$get = $client->get($url, $options);
$html = (string) $get->getBody();
if ($get->getStatusCode() !== 200 || !str_contains($html, 'Ready to book?') || !str_contains($html, 'webform-flexbox') || str_contains($html, 'name="band_preference"')) {
  throw new \RuntimeException('Booking markup failed.');
}
$doc = new DOMDocument();
@$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$data = [];
foreach ($xpath->query('//form[contains(@class,"dc-booking-form")]//input[@type="hidden"]') as $input) {
  $data[$input->getAttribute('name')] = $input->getAttribute('value');
}
$invalid = (string) $client->post($url, $options + ['form_params' => $data + ['op' => 'Submit']])->getBody();
if (!str_contains($invalid, 'This field is required')) {
  throw new \RuntimeException('Required validation failed.');
}
$bad_email = (string) $client->post($url, $options + ['form_params' => $data + ['name' => 'Booking QA', 'email' => 'invalid-email', 'legal' => 1, 'op' => 'Submit']])->getBody();
if (!str_contains($bad_email, 'is not valid. Use the format')) {
  throw new \RuntimeException('Email validation failed.');
}
$marker = 'booking-qa-' . uniqid() . '@example.invalid';
try {
  $response = $client->post($url, $options + ['form_params' => $data + ['name' => 'Booking QA', 'email' => $marker, 'legal' => 1, 'op' => 'Submit']]);
  if ($response->getStatusCode() !== 303 && $response->getStatusCode() !== 302) {
    throw new \RuntimeException('Submission did not redirect.');
  }
  if (!str_ends_with($response->getHeaderLine('Location'), '/thank-you')) {
    throw new \RuntimeException('Wrong confirmation destination.');
  }
  $ids = \Drupal::entityQuery('webform_submission')->accessCheck(FALSE)->condition('webform_id', 'booking')->execute();
  $found = FALSE;
  foreach (WebformSubmission::loadMultiple($ids) as $submission) {
    if (($submission->getData()['email'] ?? '') === $marker) {
      $found = TRUE;
      if (empty($submission->getData()['legal'])) { throw new \RuntimeException('Consent not stored.'); }
    }
  }
  if (!$found) { throw new \RuntimeException('Submission not stored.'); }
  echo "PASS: anonymous form, required fields, email validation, optional fields, persisted consent and thank-you redirect.\n";
}
finally {
  foreach (WebformSubmission::loadMultiple(\Drupal::entityQuery('webform_submission')->accessCheck(FALSE)->condition('webform_id', 'booking')->execute()) as $submission) {
    if (($submission->getData()['email'] ?? '') === $marker) { $submission->delete(); }
  }
}
