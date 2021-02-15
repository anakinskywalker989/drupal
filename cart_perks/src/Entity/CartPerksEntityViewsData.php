<?php

namespace Drupal\lfi_cart_perks\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Cart perks entity entities.
 */
class CartPerksEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
