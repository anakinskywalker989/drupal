<?php

/**
 * @file
 * Contains cart_perks_entity.page.inc.
 *
 * Page callback for Cart perks entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Cart perks entity templates.
 *
 * Default template: cart_perks_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_cart_perks_entity(array &$variables) {
  // Fetch CartPerksEntity Entity Object.
  $cart_perks_entity = $variables['elements']['#cart_perks_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
