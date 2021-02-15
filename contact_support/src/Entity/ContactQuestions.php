<?php

namespace Drupal\lfi_contact_support\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Contact questions entity.
 *
 * @ingroup lfi_contact_support
 *
 * @ContentEntityType(
 *   id = "contact_questions",
 *   label = @Translation("Contact questions"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\lfi_contact_support\ContactQuestionsListBuilder",
 *     "views_data" = "Drupal\lfi_contact_support\Entity\ContactQuestionsViewsData",
 *     "translation" = "Drupal\lfi_contact_support\ContactQuestionsTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\lfi_contact_support\Form\ContactQuestionsForm",
 *       "add" = "Drupal\lfi_contact_support\Form\ContactQuestionsForm",
 *       "edit" = "Drupal\lfi_contact_support\Form\ContactQuestionsForm",
 *       "delete" = "Drupal\lfi_contact_support\Form\ContactQuestionsDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\lfi_contact_support\ContactQuestionsHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\lfi_access\LfiAccessEntity",
 *   },
 *   base_table = "contact_questions",
 *   data_table = "contact_questions_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer contact questions entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/lfi_entity/contact_questions/{contact_questions}",
 *     "add-form" = "/admin/config/lfi_entity/contact_questions/add",
 *     "edit-form" = "/admin/config/lfi_entity/contact_questions/{contact_questions}/edit",
 *     "delete-form" = "/admin/config/lfi_entity/contact_questions/{contact_questions}/delete",
 *     "collection" = "/admin/config/lfi_entity/contact_questions",
 *   },
 *   field_ui_base_route = "contact_questions.settings"
 * )
 */
class ContactQuestions extends ContentEntityBase implements ContactQuestionsInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategory() {
    return $this->get('category')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCategory($category) {
    $this->set('category', $category);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldType() {
    return $this->get('field_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFieldType($fieldType) {
    $this->set('field_type', $fieldType);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCardinality() {
    return $this->get('cardinality')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCardinality($cardinality) {
    $this->set('cardinality', $cardinality);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMandatory() {
    return $this->get('mandatory')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMandatory($mandatory) {
    $this->set('mandatory', $mandatory);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubjectType() {
    return $this->get('subject_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubjectType($subjectType) {
    $this->set('subject_type', $subjectType);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMatchingSubject() {
    return $this->get('matching_subject')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMatchingSubject($matchingSubject) {
    $this->set('matching_subject', $matchingSubject);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority() {
    return $this->get('priority')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPriority($priority) {
    $this->set('priority', $priority);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubjectCountries() {
    $countries = [];
    foreach ($this->get('country_restriction') as $countryItem) {
      $countries[] = $countryItem->value;
    }
    return $countries;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubjectCountries(array $countries) {
    $this->set('country_restriction', $countries);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Contact questions entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Subject'))
      ->setDescription(t('The subject of contact form.'))
      ->setSettings([
        'max_length' => 100,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE);

    $fields['category'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Category'))
      ->setDescription(t('The category of subject.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['contact_categories' => 'contact_categories']])
      ->setSetting('handler', 'default:taxonomy_term')
      ->setCardinality(1)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'hidden',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'select2_entity_reference',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['field_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Additional dropdown/field'))
      ->setDescription(t('Additional dropdown/field'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -2,
      ])
      ->setCardinality(1)
      ->setSettings([
        'allowed_values' => [
          'text_field' => 'Text field',
          'product_reference' => 'Product reference',
          'file_upload' => 'File upload',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => -2,
      ])
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['cardinality'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Cardinality 0-5 of additional field'))
      ->setDescription(t('Cardinality'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -1,
      ])
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => -1,
      ])
      ->setSetting('min', 0)
      ->setSetting('max', 5)
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['mandatory'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is additional field mandatory'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Text appearing next to additional field'))
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 500,
        'text_processing' => 1,
      ])
      ->setCardinality(1)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['subject_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Select subject type'))
      ->setDescription(t('Additional dropdown/field'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setCardinality(1)
      ->setSettings([
        'allowed_values' => [
          'question' => 'Question',
          'problem' => 'Problem',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => 2,
      ])
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['matching_subject'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Matching subject:'))
      ->setDescription(t('Additional dropdown/field'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setCardinality(1)
      ->setSettings([
        'allowed_values' => [
          'activate_deactivate_account' => 'Activate/Deactivate account',
          'password_issue' => 'Password issue',
          'cancel_order' => 'Cancel order',
          'order__return_order' => 'Return order',
          'refund' => 'Refund',
          'change_order' => 'Change order',
          'website_ordering_issue' => 'Website ordering issue',
          'tracking_number' => 'Tracking number',
          'lost_package' => 'Lost package',
          'wrong_product_packed' => 'Wrong product packed',
          'missing_spare_part' => 'Missing spare part',
          'damaged_package' => 'Damaged Package',
          'product_infos' => 'Product Infos',
          'order_verification' => 'Order Verification',
          'shipping_information' => 'Shipping information',
          'extra_fee' => 'Extra fee',
          'product_authenticity_check' => 'Authenticity check',
          'warranty_registration' => 'Registration',
          'warranty_info' => 'Warranty Info',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => 3,
      ])
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['priority'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Priority'))
      ->setDescription(t('Priority of subject'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setCardinality(1)
      ->setSettings([
        'allowed_values' => [
          'low' => 'Low',
          'normal' => 'Normal',
          'high' => 'High',
          'urgent' => 'Urgent',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => 4,
      ])
      ->setDefaultValue('low')
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['country_restriction'] = BaseFieldDefinition::create('lfi_geo_country')
      ->setLabel(t('Contact subject countries'))
      ->setDescription(FALSE)
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'lfi_geo_country_formatter',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'lfi_geo_country_default',
        'weight' => 5,
      ])
      ->setRequired(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['status']->setDescription(t('A boolean indicating whether the Contact questions is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 10,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this Subject in relation to others.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'integer',
        'weight' => 100,
      ])
      ->setCardinality(1)
      ->setDisplayOptions('form', [
        'type' => 'integer',
        'weight' => 100,
      ])
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

}
