<?php

/**
 * @file
 * Contains contact_questions.page.inc.
 *
 * Page callback for Contact questions entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Contact questions templates.
 *
 * Default template: contact_questions.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_contact_questions(array &$variables) {
  // Fetch ContactQuestions Entity Object.
  $contact_questions = $variables['elements']['#contact_questions'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
