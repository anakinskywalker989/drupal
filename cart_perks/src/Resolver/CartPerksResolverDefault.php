<?php

namespace Drupal\lfi_cart_perks\Resolver;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface;
use Drupal\lfi_utility\Alzo;

/**
 * Class CartPerksResolverDefault
 *
 * @package Drupal\lfi_cart_perks\Resolver
 */
class CartPerksResolverDefault implements CartPerksResolverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a CartPerks object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $language_manager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($position, $product_id = NULL, $order_products = []) {
    // Get perks based on position placements.
    /** @var \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface[] $perks */
    $perks = $this->entityTypeManager->getStorage('cart_perks_entity')->loadByProperties(['perks_position'=> $position, 'status'=> TRUE]);

    // Get current language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Initiate default array.
    $resolved_perks = [];

    // Loop trough all perks.
    foreach ($perks as $perk){
      // Check if perk status is published and have access
      if ($perk->access('view')){

        // Load product variation for current user lng
        if ($perk->hasTranslation($langcode)) {
          $perk = $perk->getTranslation($langcode);
        }

        // Get perk positions.
        $group = $perk->get('perks_type')->value;

        // Check if countries are empty or not.
        $country = !empty($perk->country_restriction->country);

        // Determine weight.
        // Simple formula:
        // country + product - 0
        // product - 1
        // country - 2
        // global - 3
        $key = $country ? 2 : 3;

        // Get resolved perks - product level or general.
        // Get variations if any.
        $variations = $perk->getOnProduct();
        // Get bundle variations if any.
        $bundle_variations = $perk->getOnProductBundle();
        $product_variations = array_merge($variations, $bundle_variations);

        // If we have perk per product variation,
        // get product variation id for that perk.
        if($product_variations) {
          foreach ($product_variations as $key => $variation) {
            $perk_info = $this->getProductPerk($position, $perk, $variation, $product_id, $order_products);

            if (!empty($perk_info)) {
              $resolved_perks[$group][$key - 2] = $perk_info;
            }
          }
        } else {
          // Perk is not for specific product.
          // It could be though restricted per country.
          $resolved_perks[$group][$key] = [
            'title' => $perk->get('perks_title')->value,
            'description' => $perk->get('perks_text')->value,
            'desktop_image' => $this->getImageLink($perk->get('desktop_image')->target_id),
            'mobile_image' => $this->getImageLink($perk->get('mobile_image')->target_id),
            'entity' => $perk,
          ];
        }
      }
    }


    // From builded array make sort, and take first element in array
    // That should be first priority to run.
    foreach ($resolved_perks as $group => $item) {
      // Sort from lowest to highest key value.
      ksort($item);

      // Grab first element from array, this one have bigger priority.
      $resolved_perks[$group] = current($item);
    }

    return $resolved_perks;
  }

  /**
   * @param string|null $image_field_value
   *
   * @return string|null
   */
  private function getImageLink ($image_field_value) {
    if ($image_field_value !== NULL) {
      return Alzo::getFileUrl($image_field_value);
    }

    return NULL;
  }

  /**
   * Get perks details info.
   *
   * @param $position
   * @param \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface $perk
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $id
   * @param $order_products
   *
   * @return array
   */
  private function getProductPerk($position, CartPerksEntityInterface $perk, EntityInterface $entity, $id, $order_products) {
    switch ($position) {
      case 'product':
        // Product placement. We need to match product page id
        // with perk referenced product.
        if ($id !== NULL && $entity->id() === $id){
          return [
            'title'=>$perk->get('perks_title')->value,
            'description'=>$perk->get('perks_text')->value,
            'desktop_image' => $this->getImageLink($perk->get('desktop_image')->target_id),
            'mobile_image' => $this->getImageLink($perk->get('mobile_image')->target_id),
            'entity' => $perk,
          ];
        }
        break;

      default:
        // User order items product and product id needs to match.
        if (isset($order_products[$entity->id()])) {
          return [
            'title' => $perk->get('perks_title')->value,
            'description' => $perk->get('perks_text')->value,
            'desktop_image' => $this->getImageLink($perk->get('desktop_image')->target_id),
            'mobile_image' => $this->getImageLink($perk->get('mobile_image')->target_id),
            'entity' => $perk,
          ];
        }
        break;
    }

    return [];
  }

}
