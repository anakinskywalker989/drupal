<?php

namespace Drupal\lfi_cart_perks\Commands;

use Drupal\commerce_product\Entity\Product;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\Timer as TimerAlias;
use Drush\Commands\DrushCommands;

/**
 * Class Migrate.
 *
 * @package Drupal\lfi_cart_perk\Commands
 */
class MigrateToVariations extends DrushCommands {

  /**
   * The current database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * MigrateToVariations constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(Connection $database) {
    parent::__construct();

    $this->database = $database;
  }

  /**
   * Migrate Feature field from product to all product variations.
   *
   * @command lfi_cart_perks:migrate:variations
   *
   * @usage drush lfi_cart_perks:migrate:variations
   *   Merge
   *
   * @aliases perks-migrate
   *
   */
  public function migrate() {
    // Measure execution of this command.
    $timer_name = 'perks_migrate';
    $timer = new TimerAlias();
    $timer::start($timer_name);

    // Set counter.
    $counter = 0;

    // Get all products.
    $products = $this->database->select('commerce_product__field_features')
      ->fields('commerce_product__field_features', ['entity_id'])
      ->distinct()
      ->execute()->fetchAll();

    foreach ($products as $key => $id) {
      $product = Product::load($id->entity_id);
      if($product instanceof Product) {
        $field_features = $product->get('field_features')->getValue();
        $counter++;
        $variations = $product->getVariations();
        foreach ($variations as $var_key => $variation) {
          if ($variation->hasField('field_features')) {
            $variation->set('field_features', $field_features);
            $variation->save();
          }
        }
      }
    }

    // Record time.
    $time = $timer::read($timer_name);
    $timer::stop($timer_name);

    // Write message with time and memory usage.
    $message = 'Updating total: ' . $counter . ' with spent memory: ' . memory_get_usage() . ' in exact: ' . $time;
    return $this->output()->writeln($message);
  }

}
