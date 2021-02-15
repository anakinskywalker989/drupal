<?php

namespace Drupal\lfi_cart_perks\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Cart perks entity edit forms.
 *
 * @ingroup lfi_cart_perks
 */
class CartPerksEntityForm extends ContentEntityForm {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * @var EntityTypeBundleInfoInterface
   */
  protected $entity_type_bundle_info;

  /**
   * @var TimeInterface
   */
  protected $time;

  /**
   * @var EntityRepositoryInterface
   */
  protected $entity_repository;
  /**
   * Constructor.
   *
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, MessengerInterface $messenger) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\lfi_cart_perks\Entity\CartPerksEntity */
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface $entity */
    $entity = $this->entity;
    $entity->setOwnerId(\Drupal::currentUser()->id());
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger->addMessage($this->t('Created the %label Cart perks entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger->addMessage($this->t('Saved the %label Cart perks entity.', [
          '%label' => $entity->label(),
        ]));
    }

    // Clear cache for product based perks.
    if ($entity->perks_position->value === 'product') {
      if ($products = $entity->getOnProduct()) {
        $tags = [];

        /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation */
        foreach ($products as $product_variation) {
          $tags += $product_variation->getCacheTags();
        }

        Cache::invalidateTags($tags);
      }

      elseif ($bundles = $entity->getOnProductBundle()) {
        $tags = [];

        /** @var \Drupal\lfi_product_bundle\Entity\ProductBundleVariationInterface $bundle */
        foreach ($bundles as $bundle) {
          $tags += $bundle->getCacheTags();
        }

        Cache::invalidateTags($tags);
      }

      // We target global all, clearing all node_view is neccessary.
      else {
        Cache::invalidateTags(['node_view']);
      }
    }


    $form_state->setRedirect('entity.cart_perks_entity.canonical', ['cart_perks_entity' => $entity->id()]);
  }

}
