<?php

namespace Drupal\lfi_contact_support;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Contact questions entities.
 *
 * @ingroup lfi_contact_support
 */
class ContactQuestionsListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Contact questions ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\lfi_contact_support\Entity\ContactQuestions $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.contact_questions.edit_form',
      ['contact_questions' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
