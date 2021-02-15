<?php

namespace Drupal\lfi_contact_support\Service;

/**
 * Interface ContactSupportValidateInterface
 *
 * @package Drupal\lfi_contact_support\Service
 */
interface ContactSupportValidateInterface {

  /**
   * Validate form helper.
   *
   * @param $data
   * @param $language
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function validateData($data, $language);
}
