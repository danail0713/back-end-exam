<?php

/**
 * @file
 * Hooks provided by the tmgmt_deepl module.
 */

use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\JobInterface;

/**
 * Modify the DeeplTranslatorUi checkoutSettingsForm.
 *
 * @param array $form
 *   The form array.
 * @param \Drupal\tmgmt\JobInterface $job
 *   The tmgmt job entity.
 */
function hook_tmgmt_deepl_checkout_settings_form_alter(array &$form, JobInterface $job): void {
  $form['additional_info'] = [
    '#markup' => t('Additional information shown in checkoutSettingsForm'),
  ];
}

/**
 * Modify the DeeplTranslator hasCheckoutSettings method.
 *
 * @param bool $has_checkout_settings
 *   Whether job should have checkout settings.
 * @param \Drupal\tmgmt\JobInterface $job
 *   The tmgmt job entity.
 */
function hook_tmgmt_deepl_has_checkout_settings_alter(bool &$has_checkout_settings, JobInterface $job): void {
  $has_checkout_settings = TRUE;
}

/**
 * Alter deepl translation query before translation request.
 *
 * @param \Drupal\tmgmt\Entity\Job $job
 *   TMGMT Job to be used for translation.
 * @param array $query_data
 *   THe query data.
 * @param array $query_params
 *   The query parameters array.
 */
function hook_tmgmt_deepl_query_string_alter(Job $job, array &$query_data, array $query_params): void {
  if ($job->getSetting('custom_setting') == 1 && $query_params['xyz'] == 1) {
    $query_data['my_custom_var'] = '1';
  }
}
