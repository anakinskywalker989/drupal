<?php

namespace Drupal\lfi_contact_support\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Contact questions entities.
 *
 * @ingroup lfi_contact_support
 */
interface ContactQuestionsInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Contact questions name.
   *
   * @return string
   *   Name of the Contact questions.
   */
  public function getName();

  /**
   * Sets the Contact questions name.
   *
   * @param string $name
   *   The Contact questions name.
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   *   The called Contact questions entity.
   */
  public function setName($name);

  /**
   * Gets the Contact questions creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Contact questions.
   */
  public function getCreatedTime();

  /**
   * Sets the Contact questions creation timestamp.
   *
   * @param int $timestamp
   *   The Contact questions creation timestamp.
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   *   The called Contact questions entity.
   */
  public function setCreatedTime($timestamp);

  /**
   *
   * Gets the subject category.
   *
   * @return string
   */
  public function getCategory();

  /**
   *
   * Sets subject category.
   *
   * @param $category
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */

  public function setCategory($category);

  /**
   *
   * Gets the additiona field type.
   *
   * @return string
   */
  public function getFieldType();

  /**
   *
   * Sets additional field type.
   *
   * @param string $fieldType
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */

  public function setFieldType($fieldType);

  /**
   *
   * Gets cardinality of field type.
   *
   * @return int
   */
  public function getCardinality();

  /**
   *
   * Sets cardinality of field type.
   *
   * @param int $cardinality
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */
  public function setCardinality($cardinality);

  /**
   *
   * Gets boolean is field mandatory.
   *
   * @return string
   */
  public function getMandatory();

  /**
   *
   * Sets boolean for field mandatory.
   *
   * @param boolean $mandatory
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */
  public function setMandatory($mandatory);

  /**
   *
   * Gets the description of field.
   *
   * @return string
   */
  public function getDescription();

  /**
   *
   * Sets the description of field.
   *
   * @param $description
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */
  public function setDescription($description);

  /**
   *
   * Gets the subject type.
   *
   * @return string
   */
  public function getSubjectType();

  /**
   *
   * Sets the subject type.
   *
   * @param $subjectType
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */
  public function setSubjectType($subjectType);

  /**
   *
   * Gets matching subject.
   *
   * @return string
   */
  public function getMatchingSubject();

  /**
   *
   * Sets matching subject.
   *
   * @param $matchingSubject
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */
  public function setMatchingSubject($matchingSubject);

  /**
   *
   * Gets priority.
   *
   * @return string
   */
  public function getPriority();

  /**
   *
   * Sets priority.
   *
   * @param $priority
   *
   * @return \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface
   */
  public function setPriority($priority);

  /**
   * Get restricted countries.
   *
   * @return
   */
  public function getSubjectCountries();

  /**
   * Set restricted countries.
   *
   * @param array $countries
   *
   * @return
   */
  public function setSubjectCountries(array $countries);

}
