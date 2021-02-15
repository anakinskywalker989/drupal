<?php

namespace Drupal\lfi_cart_perks\Plugin\Block;

use Drupal\commerce_cart\CartProvider;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\lfi_cart_perks\Resolver\CartPerksResolverDefault;
use Drupal\lfi_product_bundle\Entity\ProductBundleVariationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CartPerksBlock' block.
 *
 * @Block(
 *  id = "cart_perks_block",
 *  admin_label = @Translation("Cart perks block"),
 * )
 */
class CartPerksBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\commerce_cart\CartProvider
   */
  protected $cartProvider;

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * @var \Drupal\lfi_cart_perks\Resolver\CartPerksResolverDefault
   */
  protected $cartPerksResolver;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, CartProvider $cartProvider, CurrentRouteMatch $routeMatch, CartPerksResolverDefault $cart_perks_resolver, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->cartProvider = $cartProvider;
    $this->routeMatch = $routeMatch;
    $this->cartPerksResolver = $cart_perks_resolver;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('current_route_match'),
      $container->get('lfi_cart_perks.resolver'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function build() {
    $build = [];
    // Default build array with cache context.
    $build['cart_text_blocks'] = [
      '#theme' => 'cart_text_blocks',
    ];

    // Get list tag cache.
    $list_tag = $this->entityTypeManager->getDefinition('cart_perks_entity')
      ->getListCacheTags();

    // Set new cache tags
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->addCacheTags($list_tag);

    // We assume no order is there.
    $current_order = NULL;
    $order_items = [];

    // Flag to determine if we need to vary by order.
    // If any cart perks for checkout placement have product referenced
    // we need to vary by order.
    $query = $this->connection->select('cart_perks_entity_field_data', 'l');
    $query->innerJoin('cart_perks_entity__on_product', 'variation', 'variation.entity_id = l.id');
    $query->innerJoin('cart_perks_entity__on_product_bundle', 'bundle_variation', 'bundle_variation.entity_id = l.id');
    $query->fields('l', []);
    $query->condition('l.perks_position', 'checkout');
    $query->condition('l.status', 1);
    $or = $query->orConditionGroup();
    $or->isNotNull('variation.on_product_target_id');
    $or->isNotNull('bundle_variation.on_product_bundle_target_id');
    $vary_by_order = $query->condition($or);
    $vary_by_order->execute()->fetchAll();

    // Try to get current order if is there.
    if (!empty($vary_by_order)) {
      // Add cache context by cart.
      $cacheable_metadata->addCacheContexts(['cart']);
      if ($current_order = $this->getCurrentOrder()) {
        // IMPORTANT. We add dependency on order even if we are not
        // sure that match exist. But we need to ensure that blocks
        // are refreshed properly, and we can do it only if order
        // is added as dependency.  This is triggered only if we have perks
        // which have products referenced.
        // Otherwise this block will have normal cache granularity by
        // country only.
        $cacheable_metadata->addCacheableDependency($current_order);
        $order_items = $this->orderProductVariations($current_order);
      }
    }

    // Load all resolved cart perks.
    $cartPerks = $this->cartPerksResolver->resolve('checkout', NULL, $order_items);

    // Loop trough perks just to add cacheability metadata.
    foreach ($cartPerks as $cartPerk) {
      $cacheable_metadata->addCacheableDependency($cartPerk['entity']);
    }

    // Set cache tags.
    $cacheable_metadata->applyTo($build);
    // Assign array to build.
    $build['cart_text_blocks']['#blocks'] = $cartPerks;

    return $build;
  }

  /**
   * Get current order if is there.
   * @return \Drupal\commerce_order\Entity\OrderInterface|\Drupal\Core\Entity\EntityInterface|mixed|null
   */
  private function getCurrentOrder() {
    $order = $this->routeMatch->getParameter('order');

    if (!empty($order)) {
      return $order;
    }

    //get items from cart
    if ($cart = $this->cartProvider->getCart('regular')) {
      if($cart instanceof Order){
        return $cart;
      }
    }

    return NULL;
  }

  /**
   * Get all (items) product variations ids from cart/checkout.
   *
   * @param $order
   *
   * @return array
   */
  private function orderProductVariations($order) {
    $result = [];

    $items = $order->getItems();

    // Get product ids.
    foreach ($items as $key => $item) {
      $product_variarion = $item->getPurchasedEntity();
      if ($product_variarion instanceof ProductVariationInterface || $product_variarion instanceof ProductBundleVariationInterface) {
        $product_variation_id = $product_variarion->id();
      }

      if (!empty($product_variation_id)) {
        $result[$product_variation_id] = $product_variation_id;
      }
    }

    return $result;
  }

}
