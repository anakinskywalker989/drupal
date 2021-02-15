<?php

namespace Drupal\lfi_cart_perks\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\lfi_product_bundle\Entity\ProductBundleInterface;
use Drupal\lfi_product_bundle\Entity\ProductBundleVariationInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Cart perks entity entities.
 *
 * @ingroup lfi_cart_perks
 */
interface CartPerksEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Cart perks entity name.
   *
   * @return string
   *   Name of the Cart perks entity.
   */
  public function getName();

  /**
   * Sets the Cart perks entity name.
   *
   * @param string $name
   *   The Cart perks entity name.
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   *   The called Cart perks entity entity.
   */
  public function setName($name);

  /**
   * Gets the Cart perks entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Cart perks entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Cart perks entity creation timestamp.
   *
   * @param int $timestamp
   *   The Cart perks entity creation timestamp.
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   *   The called Cart perks entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Cart perks entity published status indicator.
   *
   * Unpublished Cart perks entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Cart perks entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Cart perks entity.
   *
   * @param bool $published
   *   TRUE to set this Cart perks entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   *   The called Cart perks entity entity.
   */
  public function setPublished($published);

  /**
   * Return value if perk is set for specific product.
   *
   * @return
   */
  public function getOnProduct();

  /**
   * Set product entity.
   *
   * @param $product
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   */
  public function setOnProduct($product);

  /**
   * Return Product Bundle entities.
   *
   * @return \Drupal\lfi_product_bundle\Entity\ProductBundleVariationInterface[]
   */
  public function getOnProductBundle();

  /**
   * Sets Product Bundle entity.
   *
   * @param \Drupal\lfi_product_bundle\Entity\ProductBundleVariationInterface $product_bundle
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   */
  public function setOnProductBundle(ProductBundleVariationInterface $product_bundle);

  /**
   * Returns perk type as shipping/warranty/payment/guarantee.
   *
   * @return string
   */
  public function getPerksType();

  /**
   * Set perk type as position on shipping/warranty/payment/guarantee.
   *
   * @param $perks_type
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   */
  public function setPerksType($perks_type);

  /**
   * Get position where perks will be rendered product page or cart/checkout page.
   *
   * @return string
   */
  public function getPerksPosition();

  /**
   * Set position where perks will be rendered product page or cart/checkout page.
   *
   * @param $perks_position
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   */
  public function setPerksPosition($perks_position);

  /**
   * Get perk title.
   *
   * @return string
   */
  public function getCheckoutTitle();

  /**
   * Set perk title.
   *
   * @param $checkout_title
   *
   * @return \Drupal\lfi_cart_perks\Entity\CartPerksEntityInterface
   */
  public function setCheckoutTitle($checkout_title);

  /**
   * Get perk text.
   *
   * @return string
   */
  public function getCheckoutText();

  /**
   * Set perk text.
   *
   * @param $checkout_text
   *
   * @return string
   */
  public function setCheckoutText($checkout_text);

  /**
   * Get restricted countries.
   *
   * @return
   */
  public function getCartPerksCountries();

  /**
   * Set restricted countries.
   *
   * @param array $countries
   *
   * @return
   */
  public function setCartPerksCountries(array $countries);

}
