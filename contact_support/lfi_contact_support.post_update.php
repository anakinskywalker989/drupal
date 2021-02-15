<?php

/*
 * Import country mapping config.
 */
function lfi_contact_support_post_update_1() {

  /** @var \Drupal\commerce\Config\ConfigUpdaterInterface $config_updater */
  $config_updater = \Drupal::service('commerce.config_updater');

  $ccTeams = [
    'lfi_contact_support.mail_country_mapping.emea',
    'lfi_contact_support.mail_country_mapping.latam',
    'lfi_contact_support.mail_country_mapping.row',
    'lfi_contact_support.mail_country_mapping.us_ca',
  ];
  $result = $config_updater->revert($ccTeams, FALSE);

  $success_results = $result->getSucceeded();
  $failure_results = $result->getFailed();
  if ($success_results) {
    $message = t('Succeeded:') . '<br>';
    foreach ($success_results as $success_message) {
      $message .= $success_message . '<br>';
    }
    $message .= '<br>';
  }
  if ($failure_results) {
    $message .= t('Failed:') . '<br>';
    foreach ($failure_results as $failure_message) {
      $message .= $failure_message . '<br>';
    }
  }

  return $message;
}
