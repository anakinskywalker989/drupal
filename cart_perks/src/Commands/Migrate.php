<?php

namespace Drupal\lfi_cart_perks\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\lfi_cart_perks\Entity\CartPerksEntity;
use Drush\Commands\DrushCommands;

/**
 * Class Migrate.
 *
 * @package Drupal\lfi_cart_perk\Commands
 */
class Migrate extends DrushCommands {

  /**
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(LanguageManager $language_manager, ConfigFactoryInterface $configFactory) {
    parent::__construct();
    $this->languageManager = $language_manager;
    $this->configFactory = $configFactory;
  }

  /**
   * Migrate old cart perks from configuration
   *
   * @command lfi_cart_perks:migrate
   *
   * @usage drush lfi_cart_perks:migrate
   *   Merge
   *
   * @aliases lcpm
   *
   */
  public function migrate() {
    // Fetch all config per language values.
    $language_manager = $this->languageManager;
    $languages = $language_manager->getLanguages();

    $data = [];
    foreach ($languages as $language) {
      // Change language for the configuration.
      $language_manager->setConfigOverrideLanguage($language);

      // Load configuration.
      $perks_config = $this->configFactory->get('lfi_commerce.cart_settings')->get();

      // Loop trough perks to get configuration.
      foreach ($perks_config as $key => $perk_config) {
        if (in_array($key, ['shipping', 'warranty', 'payment'])) {
          $data[$key][$language->getId()] = $perk_config;
        }
      }
    }

    foreach ($data as $type => $subdata) {
      // Create entity, default in english.
      $perk_entity = CartPerksEntity::create([
        'name' => ucfirst($type) . ' global cart/checkout block',
        'perks_type' => $type,
        'perks_position' => 'checkout',
        'perks_title' => $subdata['en']['title'],
        'perks_text' => $subdata['en']['description'],
        'status' => 1
      ]);

      // Add available translations.
      foreach ($subdata as $lang_key => $subsubdata) {
        if ($lang_key !== 'en') {
          $translated = [
            'perks_title' => $subdata[$lang_key]['title'],
            'perks_text' => $subdata[$lang_key]['description'],
          ];
          $perk_entity->addTranslation($lang_key, $translated);
        }
      }

      $perk_entity->save();
    }

    return $this->output()
      ->writeln('Success');

  }
  
}
