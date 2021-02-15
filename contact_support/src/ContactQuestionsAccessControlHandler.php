<?php

namespace Drupal\lfi_contact_support;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Contact questions entity.
 *
 * @see \Drupal\lfi_contact_support\Entity\ContactQuestions.
 */
class ContactQuestionsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\lfi_contact_support\Entity\ContactQuestionsInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished contact questions entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published contact questions entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit contact questions entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete contact questions entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add contact questions entities');
  }

}
