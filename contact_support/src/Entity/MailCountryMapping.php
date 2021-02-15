<?php

namespace Drupal\lfi_contact_support\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Mail country mapping entity.
 *
 * @ConfigEntityType(
 *   id = "mail_country_mapping",
 *   label = @Translation("Mail country mapping"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\lfi_contact_support\MailCountryMappingListBuilder",
 *     "form" = {
 *       "add" = "Drupal\lfi_contact_support\Form\MailCountryMappingForm",
 *       "edit" = "Drupal\lfi_contact_support\Form\MailCountryMappingForm",
 *       "delete" = "Drupal\lfi_contact_support\Form\MailCountryMappingDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\lfi_contact_support\MailCountryMappingHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "mail_country_mapping",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "cc_team",
 *     "countries",
 *     "email",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/mail_country_mapping/{mail_country_mapping}",
 *     "add-form" = "/admin/structure/mail_country_mapping/add",
 *     "edit-form" = "/admin/structure/mail_country_mapping/{mail_country_mapping}/edit",
 *     "delete-form" = "/admin/structure/mail_country_mapping/{mail_country_mapping}/delete",
 *     "collection" = "/admin/structure/mail_country_mapping"
 *   }
 * )
 */
class MailCountryMapping extends ConfigEntityBase implements MailCountryMappingInterface {

  /**
   * Name of CC team.
   *
   * @var string
   */
  protected $cc_team;

  /**
   * List of countries in CC team.
   *
   * @var array
   */
  protected $countries;

  /**
   * CC team email.
   *
   * @var string
   */
  protected $email;

  /**
   * {@inheritdoc}
   */
  public function getCCteam() {
    return $this->cc_team;
  }

  /**
   * {@inheritdoc}
   */
  public function getCountries() {
    return $this->countries;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    return $this->email;
  }
}
