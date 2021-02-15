<?php

namespace Drupal\lfi_cart_perks;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Cart perks entity entities.
 *
 * @ingroup lfi_cart_perks
 */
class CartPerksEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Cart perks entity ID');
    $header['name'] = $this->t('Name');
    $header['type'] = $this->t('Type');
    $header['position'] = $this->t('Position');
    $header['product_variations'] = $this->t('Product Variations IDs');
    $header['product_bundle_variations'] = $this->t('Product Bundle Variations IDs');
    $header['country_restriction'] = $this->t('Restricted countries');
    $header['status'] = $this->t('Active status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\lfi_cart_perks\Entity\CartPerksEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.cart_perks_entity.edit_form',
      ['cart_perks_entity' => $entity->id()]
    );
    $row['type'] = $entity->get('perks_type')->value;
    $row['position'] = $entity->get('perks_position')->value;

    $product_variations_implode = [];
    $product_variations = $entity->get('on_product')->getValue();
    foreach ($product_variations as $product_variation) {
      $product_variations_implode[] = $product_variation['target_id'];
    }
    $row['product_variations'] = implode(', ', $product_variations_implode);

    $bundle_variations_implode = [];
    $bundle_variations = $entity->get('on_product_bundle')->getValue();
    foreach ($bundle_variations as $bundle_variation) {
      $bundle_variations_implode[] = $bundle_variation['target_id'];
    }
    $row['product_bundle_variations'] = implode(', ', $bundle_variations_implode);;

    $row['country_restriction'] = $entity->get('country_restriction')->country ? implode(':', $entity->get('country_restriction')->country) : NULL;
    $row['status'] = $entity->get('status')->value;
    return $row + parent::buildRow($entity);
  }

}
