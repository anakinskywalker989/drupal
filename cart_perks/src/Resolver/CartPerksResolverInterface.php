<?php

namespace Drupal\lfi_cart_perks\Resolver;

/**
 * Interface CartPerksResolverInterface
 *
 * @package Drupal\lfi_cart_perks\Resolver
 */
interface CartPerksResolverInterface {

  /**
   * Resolve which perks should be displayd per specific position.
   *
   * @param string $position
   *   Position where perks should be displayed.
   * @param string $product_id
   *   Optional to target per product id.
   * @param array $order_items
   *   Current order items from cart.
   *
   * @return array
   *   Return array of perks grouped by type.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function resolve($position, $product_id, $order_items);

}
