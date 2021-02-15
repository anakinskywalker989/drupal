<?php

namespace Drupal\lfi_contact_support\Service;

/**
 * Interface ContactSupportServiceInterface
 *
 * @package Drupal\lfi_contact_support\Service
 */
interface ContactSupportServiceInterface {

  /**
   * Get subjects.
   *
   * @param $language
   * @param $category
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getSubjects($language, $category);

  /**
   * Get categories.
   *
   * @param $language
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCategories($language);

  /**
   * Get products.
   *
   * @param $language
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getProducts($language);

  /**
   * Get cc team.
   *
   * @param $user_country
   *
   * @return string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function ccTeam($user_country);

  /**
   * Prepare zendesk data.
   *
   * @param $data
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function prepareZendeskData($data);

  /**
   * Prepare zendesk ticket.
   *
   * @param $data
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function createZendeskTicket($data);

  /**
   * Upload image to zendesk.
   *
   * @param $filename
   *
   * @return array|mixed
   */
  public function uploadImageToZendesk($filename);

  /**
   * Delete image from AWS.
   *
   * @param $data
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function deleteAwsImage($data);

  /**
   * Get image from AWS.
   *
   * @param $filename
   *
   * @return mixed
   */
  public function getAwsImage($filename);

  /**
   * Helper function for label translations.
   *
   * @param $string
   * @param $language
   *
   * @return false|string
   */
  public function getTranslatedLabels($string, $language);

}
