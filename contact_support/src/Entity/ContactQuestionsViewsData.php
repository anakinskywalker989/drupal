<?php

namespace Drupal\lfi_contact_support\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Contact questions entities.
 */
class ContactQuestionsViewsData extends EntityViewsData {

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
