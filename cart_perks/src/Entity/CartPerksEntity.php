<?php

namespace Drupal\lfi_cart_perks\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lfi_product_bundle\Entity\ProductBundleVariationInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Cart perks entity entity.
 *
 * @ingroup lfi_cart_perks
 *
 * @ContentEntityType(
 *   id = "cart_perks_entity",
 *   label = @Translation("Cart perks entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\lfi_cart_perks\CartPerksEntityListBuilder",
 *     "views_data" = "Drupal\lfi_cart_perks\Entity\CartPerksEntityViewsData",
 *     "translation" = "Drupal\lfi_cart_perks\CartPerksEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\lfi_cart_perks\Form\CartPerksEntityForm",
 *       "add" = "Drupal\lfi_cart_perks\Form\CartPerksEntityForm",
 *       "edit" = "Drupal\lfi_cart_perks\Form\CartPerksEntityForm",
 *       "delete" = "Drupal\lfi_cart_perks\Form\CartPerksEntityDeleteForm",
 *     },
 *     "access" = "Drupal\lfi_access\LfiAccessEntity",
 *     "route_provider" = {
 *       "html" = "Drupal\lfi_cart_perks\CartPerksEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "cart_perks_entity",
 *   data_table = "cart_perks_entity_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer cart perks entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/lfi_entity/cart_perks_entity/{cart_perks_entity}",
 *     "add-form" = "/admin/config/lfi_entity/cart_perks_entity/add",
 *     "edit-form" = "/admin/config/lfi_entity/cart_perks_entity/{cart_perks_entity}/edit",
 *     "delete-form" = "/admin/config/lfi_entity/cart_perks_entity/{cart_perks_entity}/delete",
 *     "collection" = "/admin/config/lfi_entity/cart_perks_entity",
 *   },
 *   field_ui_base_route = "cart_perks_entity.settings"
 * )
 */
class CartPerksEntity extends ContentEntityBase implements CartPerksEntityInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

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
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPerksType() {
    return $this->get('perks_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPerksType($perks_type) {
    $this->set('perks_type', $perks_type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPerksPosition() {
    return $this->get('perks_position')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPerksPosition($perks_position) {
    $this->set('perks_position', $perks_position);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCheckoutTitle() {
    return $this->get('perks_title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCheckoutTitle($checkout_title) {
    $this->set('perks_title', $checkout_title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCheckoutText() {
    return $this->get('perks_text')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCheckoutText($checkout_text) {
    $this->set('perks_text', $checkout_text);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCartPerksCountries() {
    $countries = [];
    foreach ($this->get('country_restriction') as $countryItem) {
      $countries[] = $countryItem->value;
    }
    return $countries;
  }

  /**
   * {@inheritdoc}
   */
  public function setCartPerksCountries(array $countries) {
    $this->set('country_restriction', $countries);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOnProduct() {
    return $this->get('on_product')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setOnProduct($product) {
    $this->set('on_product', $product);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOnProductBundle() {
    return $this->get('on_product_bundle')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setOnProductBundle(ProductBundleVariationInterface $product_bundle) {
    $this->set('on_product_bundle', $product_bundle);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Cart perks entity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(FALSE)
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

    $fields['on_product'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Select product variation'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'commerce_product_variation')
      ->setSetting('handler', 'default:commerce_product_variation')
      ->setTranslatable(FALSE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'select2_entity_reference',
        'autocomplete' => FALSE,
        'settings' => [
          'autocomplete' => FALSE,
        ],
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['on_product_bundle'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Select product bundle variation'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'commerce_bundle_variation')
      ->setSetting('handler', 'default:commerce_bundle_variation')
      ->setTranslatable(FALSE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'lfi_select2_entity_reference',
        'autocomplete' => FALSE,
        'settings' => [
          'autocomplete' => FALSE,
        ],
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Cart perks entity entity.'))
      ->setSettings([
        'max_length' => 50,
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
      ->setTranslatable(FALSE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Cart perks entity is published.'))
      ->setDefaultValue(TRUE)
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

    $fields['perks_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Perks type'))
      ->setDescription(t('Checkout Step.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setCardinality(1)
      ->setSettings([
        'allowed_values' => [
          'shipping' => 'Shipping',
          'warranty' => 'Warranty',
          'payment' => 'Payment',
          'guarantee' => 'Guarantee',
        ],
      ])
      ->setDefaultValue('shipping')
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => -3,
      ])
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['perks_position'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Perks position'))
      ->setDescription(t('Checkout Step.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -2,
      ])
      ->setSettings([
        'allowed_values' => [
          'checkout' => 'Checkout',
          'product' => 'Product',
        ],
      ])
      ->setDefaultValue('checkout')
      ->setDisplayOptions('form', [
        'type' => 'radios',
        'weight' => -2,
      ])
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['perks_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The name of the Cart perks entity entity.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 128,
        'text_processing' => 0,
      ])
      ->setDefaultValue ('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['perks_text'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Text'))
      ->setDescription(t('The name of the Cart perks entity entity.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 600,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['desktop_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Desktop Image'))
      ->setRevisionable(FALSE)
      ->setSettings([
        'uri_scheme' => 'public',
        'target_type' => 'file',
        'display_field' => FALSE,
        'display_default' => FALSE,
        'text_processing' => 1,
      ])
      ->setCardinality(1)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image_url',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['mobile_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Mobile Image'))
      ->setRevisionable(FALSE)
      ->setSettings([
        'uri_scheme' => 'public',
        'target_type' => 'file',
        'display_field' => FALSE,
        'display_default' => FALSE,
        'text_processing' => 1,
      ])
      ->setCardinality(1)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image_url',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['country_restriction'] = BaseFieldDefinition::create('lfi_geo_country')
      ->setLabel(t('Cart perks countries'))
      ->setDescription(FALSE)
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'lfi_geo_country_formatter',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'lfi_geo_country_default',
        'weight' => 2,
      ])
      ->setRequired(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    return $fields;
  }

}
